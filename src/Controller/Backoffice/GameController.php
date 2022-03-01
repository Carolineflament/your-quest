<?php

namespace App\Controller\Backoffice;

use App\Entity\Game;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Service\CascadeTrashed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/back/jeux")
 */
class GameController extends AbstractController
{
    /**
     * @Route("/", name="app_backoffice_game_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository): Response
    {

        //On vérifie que le jeu à afficher appartient au joueur
        //find by user.id avec user
        //connecte user = blablabla


        return $this->render('backoffice/game/index.html.twig', [
            'games' => $gameRepository->findAll(),
        ]);
    }

    /**
     * @Route("/nouveau", name="app_backoffice_game_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($game);
            $entityManager->flush();

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
    public function show(Game $game, User $user): Response
    {
        return $this->render('backoffice/game/show.html.twig', [
            'game' => $game,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="app_backoffice_game_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_game_delete", methods={"POST"})
     */
    public function delete(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $entityManager->remove($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/status/{id}",name="app_backoffice_update_status", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @param Game $game
     * @return void
     */
    public function update_status(Game $game, EntityManagerInterface $entityManager, CascadeTrashed $cascadeTrashed)
    {
        if($game->getStatus())
        {
            $cascadeTrashed->trashGame($game);
            $game->setStatus(false);
            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été supprimé ! Tous ses jeux, checkpoints, questions et instances ont été mis à la poubelle !'
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
        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }
}
