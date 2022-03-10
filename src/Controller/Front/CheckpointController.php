<?php

namespace App\Controller\Front;

use App\Entity\Checkpoint;
use App\Entity\Enigma;
use App\Entity\Instance;
use App\Entity\Round;
use App\Entity\ScanQR;
use App\Repository\AnswerRepository;
use App\Repository\CheckpointRepository;
use App\Repository\RoundRepository;
use App\Repository\ScanQRRepository;
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
    public function check(Checkpoint $checkpointScan, string $token, SessionInterface $session, CheckpointRepository $checkpointRepos, RoundRepository $roundRepos, EntityManagerInterface $entityManager, ScanQRRepository $scanQRRepos) : Response
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
            $current_instance = new Instance();
            $date = new DateTime();
            $date = $date->getTimestamp();
            /* This is a way to check if the user is in the right instance.
                    If the user is in the right instance, we check if the game is finished.
                    If the game is finished, we redirect the user to the main page.
                    If the game is not finished, we create a new round. */
            foreach($checkpointScan->getGame()->getInstances() AS $instance)
            {
                if($date > $instance->getStartAt()->getTimestamp() && $date < $instance->getEndAt()->getTimestamp())
                {
                    $has_instance = true;
                    $current_instance = $instance;
                    break;
                }
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
                return $this->redirectToRoute('front_main', [], Response::HTTP_SEE_OTHER);
            }
        }

        /** This is a way to check if the user has already scanned the checkpoint. 
        * if not we create the scan at the time
        */
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
            $checkpointScan = $lastScanAt->getCheckpoint();
            
            $this->addFlash(
                'notice-danger',
                'Vous avez déjà flashé ce checkpoint !'
            );
        }

        // Check si c'est le dernier checkPoint pour mettre le EndAt
        if($key_checkpointScan[0] === count($checkpoints)-1)
        {
            $round->setEndAt(new \DateTimeImmutable());
            $entityManager->persist($round);
            $this->addFlash(
                'notice-success',
                'Bravo vous avez terminé le jeu :) !'
            );
        }
        $entityManager->flush();

        return $this->render('front/checkpoint/check.html.twig', [
            'enigmas' => $checkpointScan->getEnigmas(),
            'message' => $checkpointScan->getSuccessMessage()
        ]);
    }

    /**
     * @Route("/checkpoint/enigma/{id}", name="_response", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function response(Enigma $enigma, AnswerRepository $answerRepository): Response
    {
        $checkpoint = $enigma->getCheckpoint();
        $good_answer = $answerRepository->findOneBy(['enigma' => $enigma, 'status' => true, 'isTrashed' => false]);

        $type_response = '';
        if($good_answer->getAnswer() == $_POST['enigma-'.$enigma->getId()])
        {
            $type_response = 'good';
            $this->addFlash(
                'notice-success',
                'Bravo c\'était la bonne réponse :) !'
            );
        }
        else
        {
            $type_response = 'wrong';
            $this->addFlash(
                'notice-danger',
                'Loupé pour cette fois, la bonne réponse était : '.$good_answer->getAnswer().', mais comme on est sympa tu peux te rendre au prochain checkpoint dont voici l\indice !'
            );
        }

        return $this->render('front/checkpoint/check.html.twig', [
            'type_response' => $type_response,
            'message' => $checkpoint->getSuccessMessage(),
            'enigmas' => array(),
        ]);
    }
}
