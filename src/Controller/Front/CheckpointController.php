<?php

namespace App\Controller\Front;

use App\Entity\Checkpoint;
use App\Entity\Enigma;
use App\Entity\Game;
use App\Entity\Instance;
use App\Entity\Round;
use App\Entity\ScanQR;
use App\Entity\UserAnswer;
use App\Repository\AnswerRepository;
use App\Repository\CheckpointRepository;
use App\Repository\RoundRepository;
use App\Repository\ScanQRRepository;
use App\Repository\UserAnswerRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/", name="front_checkpoint")
 */
class CheckpointController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator;
   
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * @Route("/qrscan", name="", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('front/checkpoint/index.html.twig', [
            'controller_name' => 'CheckpointController',
        ]);
    }

    /**
     * @Route("/checkpoint/{id}/{token}", name="_check", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function check(Checkpoint $checkpointScan, string $token, SessionInterface $session, CheckpointRepository $checkpointRepos, RoundRepository $roundRepos, EntityManagerInterface $entityManager, ScanQRRepository $scanQRRepos, UserAnswerRepository $userAnswerRepository) : Response
    {
        /* This is a way to check if the user is trying to cheat. */
        if(sha1($checkpointScan->getTitle()) !== $token)
        {
            $this->addFlash(
                'notice-danger',
                'C\'est pas bien de tricher ;) !'
            );
            return $this->redirectToRoute('front_main', [], Response::HTTP_SEE_OTHER);
        }
        $user = $this->getUser();

        /* This is a way to redirect the user to the login page if he is not logged in. */
        if($user === null)
        {
            $this->addFlash(
                'notice-danger',
                'Merci de vous connecter ou de vous inscrire afin de participer au jeu ;)'
            );
            $session->set('route_redirect', $this->urlGenerator->generate('front_checkpoint_check', ['id' => $checkpointScan->getId(), 'token' => sha1($checkpointScan->getTitle())]));
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        
        if($checkpointScan->getGame()->getStatus() === false)
        {
            $this->addFlash(
                'notice-danger',
                'Le jeu n\'est pas ouvert, merci de revenir lorsque le jeu sera ouvert !'
            );
            return $this->redirectToRoute('front_main', [], Response::HTTP_SEE_OTHER);
        }
        else
        {
            $has_instance = false;
            $current_instance = $this->getCurrentInstance($checkpointScan->getGame());
            if($current_instance->getId() !== null)
            {
                $has_instance = true;
            }
            
            if(!$has_instance)
            {
                $this->addFlash(
                    'notice-danger',
                    'Aucune instance ouverte, merci de revenir lorsque le jeu sera ouvert !'
                );
                return $this->redirectToRoute('front_main', [], Response::HTTP_SEE_OTHER);
            }
        }

        /* This is a way to check if the user has already played the game.
                If he has played, we check if the game is finished.
                If the game is finished, we redirect the user to the main page.
                If the game is not finished, we create a new round. */
        $round = $roundRepos->findOneBy(['user' => $user, 'instance' => $current_instance]);
        if($round !== null)
        {
            if($round->getEndat() !== null)
            {
                $this->addFlash(
                    'notice-danger',
                    'Votre partie est terminée, vous ne pouvez pas rejouer !'
                );
                return $this->redirectToRoute('front_main', [], Response::HTTP_SEE_OTHER);
            }
        }
        else
        {
            $round = new Round();
            $round->setInstance($current_instance);
            $round->setUser($user);
            $round->setStartAt(new \DateTimeImmutable());
            $entityManager->persist($round);
        }

        $checkpoints = $checkpointRepos->findBy(['game' => $checkpointScan->getGame()], ['orderCheckpoint' => 'ASC']);
        $key_checkpointScan = array_keys($checkpoints, $checkpointScan);

        // Check if the previous checkpoint has been scanned
        /* It's a loop that check if the previous checkpoint has been scanned. */
        for($i = 0; $i < $key_checkpointScan[0]; $i++)
        {
            $is_scan = $scanQRRepos->findOneBy(['round' => $round, 'checkpoint' => $checkpoints[$i]]);
            if($is_scan === null)
            {
                $this->addFlash(
                    'notice-danger',
                    'Tentative de triche ! :) Vous n\'avez pas scanné les checkpoints précédent, revenez sur vos pas !'
                );
                return $this->redirectToRoute('front_checkpoint', [], Response::HTTP_SEE_OTHER);
            }
            else
            {
                $enigmas = $checkpoints[$i]->getUnTrashedEnigmas();
                $enigma_non_response = null;
                foreach($enigmas AS $enigma)
                {
                    $userAnswer = $userAnswerRepository->findBy(['user' => $user, 'enigma' => $enigma, 'isGood' => true]);
                    if(count($userAnswer) === 0)
                    {
                        $enigma_non_response = $enigma;
                        $this->addFlash(
                            'notice-warning',
                            'Vous avez scanné le checkpoint '.$checkpointScan->getTitle().' mais vous n\'avez pas répondu à la question du checkpoint '.$checkpoints[$i]->getTitle().', vous grillez les étapes :) !'
                        );
                        return $this->render('front/checkpoint/check.html.twig', [
                            'enigma' => $enigma_non_response,
                            'message' => $checkpointScan->getSuccessMessage()
                        ]);
                    }
                }
            }
        }

        /** This is a way to check if the user has already scanned the checkpoint. 
        * if not we create the scan at the time
        */
        $has_already_flash = false;
        $scan = $scanQRRepos->findOneBy(['round' => $round, 'checkpoint' => $checkpointScan]);
        if($scan === null)
        {
            $scan = new ScanQR();
            $scan->setCheckpoint($checkpointScan);
            $scan->setRound($round);
            $scan->setScanAt(new \DateTimeImmutable());
            $entityManager->persist($scan);
        }
        else
        {
            /* This is a way to get the last checkpoint scanned by the user. */
            $lastScanAt = $scanQRRepos->findOneBy(['round' => $round], ['scanAt' => 'DESC']);
            //$checkpointScan = $lastScanAt->getCheckpoint();
            $has_already_flash = true;
            $this->addFlash(
                'notice-success',
                'Vous avez déjà flashé ce checkpoint !'
            );
        }

        // Check si c'est le dernier checkPoint pour mettre le EndAt
        if($key_checkpointScan[0] === count($checkpoints)-1)
        {
            $entityManager->persist($round);
            if(count($checkpointScan->getUnTrashedEnigmas()) == 0)
            {
                $round->setEndAt(new \DateTimeImmutable());
                $this->addFlash(
                    'notice-success',
                    'Bravo vous avez terminé le jeu :) !'
                );
            }
            else
            {
                $this->addFlash(
                    'notice-success',
                    'Dernière énigme :) !'
                );
            }
        }
        $entityManager->flush();

        
        $enigmas = $checkpointScan->getUnTrashedEnigmas();
        $enigma_non_response = null;
        foreach($enigmas AS $enigma)
        {
            $userAnswer = $userAnswerRepository->findBy(['user' => $user, 'enigma' => $enigma, 'isGood' => true]);
            if(count($userAnswer) === 0)
            {
                $enigma_non_response = $enigma;
                if($has_already_flash)
                {
                    $this->addFlash(
                        'notice-warning',
                        'Mais vous n\'avez pas répondu à la question :) !'
                    );
                }
                break;
            }
        }


        return $this->render('front/checkpoint/check.html.twig', [
            'enigma' => $enigma_non_response,
            'message' => $checkpointScan->getSuccessMessage()
        ]);
    }

    /**
     * @Route("/checkpoint/enigma/{id}", name="_response", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function response(Enigma $enigma, AnswerRepository $answerRepository, EntityManagerInterface $entityManager, CheckpointRepository $checkpointRepos, RoundRepository $roundRepos): Response
    {
        $checkpoint = $enigma->getCheckpoint();
        $good_answer = $answerRepository->findOneBy(['enigma' => $enigma, 'status' => true, 'isTrashed' => false]);

        $type_response = '';
        $enigma_response = null;

        $userAnswer = new UserAnswer();
        $userAnswer->setEnigma($enigma);
        $userAnswer->setAnswer($good_answer);
        $userAnswer->setUser($this->getUser());

        if($good_answer->getAnswer() == $_POST['enigma-'.$enigma->getId()])
        {
            $userAnswer->setIsGood(true);
            $type_response = 'good';
            $this->addFlash(
                'notice-success',
                'Bravo c\'était la bonne réponse :) !'
            );

            $current_instance = $this->getCurrentInstance($checkpoint->getGame());
            
            $round = $roundRepos->findOneBy(['user' => $this->getUser(), 'instance' => $current_instance]);
            $checkpoints = $checkpointRepos->findBy(['game' => $checkpoint->getGame()], ['orderCheckpoint' => 'ASC']);
            $key_checkpointScan = array_keys($checkpoints, $checkpoint);

            if($key_checkpointScan[0] === count($checkpoints)-1)
            {
                $round->setEndAt(new \DateTimeImmutable());
                $entityManager->persist($round);
            }
        }
        else
        {
            $userAnswer->setIsGood(false);
            $enigma_response = $enigma;
            $type_response = 'wrong';
            $this->addFlash(
                'notice-danger',
                'Mauvaise réponse, mais tu peux retenter ta chance !'
            );
        }

        $entityManager->persist($userAnswer);
        $entityManager->flush();
        
        return $this->render('front/checkpoint/check.html.twig', [
            'type_response' => $type_response,
            'message' => $checkpoint->getSuccessMessage(),
            'enigma' => $enigma_response,
        ]);
    }

    private function getCurrentInstance(Game $game): Instance
    {
        $current_instance = new Instance();
        $date = new DateTime();
        $date = $date->getTimestamp();
        foreach($game->getInstances() AS $instance)
        {
            if($date > $instance->getStartAt()->getTimestamp() && $date < $instance->getEndAt()->getTimestamp())
            {
                $current_instance = $instance;
                break;
            }
        }

        return $current_instance;
    }
}
