<?php

namespace App\Controller\Backoffice;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
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
        //TODO Does the game belong to the organizer?
        // $gameUser = $this->getUser();
        // if ($gameUser !== $game->getUser()) {
        //     throw $this->createAccessDeniedException('Non autorisé.');
        // }

        return $this->render('backoffice/game/index.html.twig', [
            'actives_games' => $gameRepository->findBy(['status' => 1, 'isTrashed' => 0]),
            'inactives_games' => $gameRepository->findBy(['status' => 0, 'isTrashed' => 0]),
        ]);
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
                'Le jeu '.$game->getTitle().' a été créé !'
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
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été modifié !'
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
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token')))
        {
            $cascadeTrashed->trashGame($game);
            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été supprimé ! Tous ses checkpoints, questions et instances ont été mis à la poubelle !'
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
        if ($this->isCsrfTokenValid('trash'.$game->getId(), $request->request->get('_token')))
        {
            if($game->getStatus())
            {
                $game->setStatus(false);
                $this->addFlash(
                    'notice-success',
                    'Le jeu '.$game->getTitle().' a été désactivé !'
                );
            }
            else
            {
                $game->setStatus(true);
                $this->addFlash(
                    'notice-success',
                    'Le jeu '.$game->getTitle().' a été activé !'
                );
            }

            $entityManager->flush();
        } 
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de désactiver le jeu '.$game->getTitle().', token invalide !'
            );
        }
        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }
}
