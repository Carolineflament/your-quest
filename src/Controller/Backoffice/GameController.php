<?php

namespace App\Controller\Backoffice;

use App\Entity\Game;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Security\Voter\GameVoter;
use App\Service\CascadeTrashed;
use App\Service\MySlugger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @Route("/back/jeux")
 */
class GameController extends AbstractController
{
    private $paramBag;

    public function __construct(ParameterBagInterface $paramBag)
    {
        $this->paramBag = $paramBag;
    }
    /**
     * @Route("/", name="app_backoffice_game_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository): Response
    {
        // It's getting the user id of the user connected.
        $userConnected = $this->getUser()->getId();
        //dump($userConnected);
        $gameUser = $gameRepository->findBy(['user' => $userConnected]);
        $gameUserCreated = $gameUser[0];
        $thisPlayersGames= $gameUserCreated->getUser()->getId();
        //dump($thisUser);

        // It's checking if the user connected is the owner of the game.
        if ($userConnected == $thisPlayersGames)
        {
            return $this->render('backoffice/game/index.html.twig', [
                'actives_games' => $gameRepository->findBy(['status' => 1, 'isTrashed' => 0, 'user' => $userConnected]),
                'inactives_games' => $gameRepository->findBy(['status' => 0, 'isTrashed' => 0, 'user' => $userConnected]),
            ]);
        }
    }

    /**
     * @Route("/nouveau", name="app_backoffice_game_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MySlugger $mySlugger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $mySlugger->slugify($game->getTitle());
            $game->setSlug($slug);
            $game->setUser($this->getUser());
            
            $file = $form['image']->getData();
            $filename = $slug.'.'.$file->guessExtension();
            $file->move($this->paramBag->get('app.game_images_directory'), $filename);
            $game->setImage($filename);
            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a ??t?? cr???? !'
            );

            return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/game/new.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{slug}", name="app_backoffice_game_show", methods={"GET"})
     */
    public function show(Game $game): Response
    {
        $instances = $game->getUnTrashedInstances()->getValues();
        $date = new DateTime();
        $date = $date->getTimestamp();
        foreach($instances AS $key=> $instance)
        {
            if($date > $instance->getStartAt()->getTimestamp() && $date < $instance->getEndAt()->getTimestamp())
            {
                unset($instances[$key]);
                array_unshift($instances, $instance);
            }
        }
        return $this->render('backoffice/game/show.html.twig', [
            'game' => $game,
            'instances' => $instances
        ]);
    }

    /**
     * @Route("/{slug}/modifier", name="app_backoffice_game_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('EDIT_GAME', $game);

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a ??t?? modifi?? !'
            );

            return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_game_delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Request $request, Game $game, CascadeTrashed $cascadeTrashed): Response
    {

        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('DELETE_GAME', $game); 

        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token')))
        {
            $cascadeTrashed->trashGame($game);
            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a ??t?? supprim?? ! Tous ses checkpoints, questions et instances ont ??t?? mis ?? la poubelle !'
            );
        } 
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de supprimer le jeu '.$game->getTitle().', token invalide !'
            );

        }

        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/status/{id}",name="app_backoffice_update_status", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @param Game $game
     * @return void
     */
    public function update_status(Request $request, Game $game, EntityManagerInterface $entityManager)
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('EDIT_GAME', $game);

        if ($this->isCsrfTokenValid('trash'.$game->getId(), $request->request->get('_token')))

        {
            if($game->getStatus())
            {
                $game->setStatus(false);
                $this->addFlash(
                    'notice-success',
                    'Le jeu '.$game->getTitle().' a ??t?? d??sactiv?? !'
                );
            }
            else
            {
                $game->setStatus(true);
                $this->addFlash(
                    'notice-success',
                    'Le jeu '.$game->getTitle().' a ??t?? activ?? !'
                );
            }

            $entityManager->flush();
        } 
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de d??sactiver le jeu '.$game->getTitle().', token invalide !'
            );
        }
        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }
}
