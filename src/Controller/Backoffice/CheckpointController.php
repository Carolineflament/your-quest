<?php

namespace App\Controller\Backoffice;

use App\Entity\Checkpoint;
use App\Entity\Game;
use App\Form\CheckpointType;
use App\Repository\CheckpointRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backoffice/checkpoint")
 */
class CheckpointController extends AbstractController
{
    /**
     * @Route("/jeu/{gameSlug}", name="backoffice_checkpoint_index", methods={"GET"})
     */
    public function index($gameSlug, GameRepository $gameRepository, CheckpointRepository $checkpointRepository): Response
    {
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        return $this->render('backoffice/checkpoint/index.html.twig', [
            'checkpoints' => $checkpointRepository->findBy(['game' => $game]),
            'game' =>$game,
        ]);
    }

    /**
     * @Route("/jeu/{gameSlug}/nouveau", name="backoffice_checkpoint_new", methods={"GET", "POST"})
     */
    public function new($gameSlug, GameRepository $gameRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $checkpoint = new Checkpoint();
        $form = $this->createForm(CheckpointType::class, $checkpoint);
        $form->handleRequest($request);

        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        $checkpoint->setGame($game);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($checkpoint);
            $entityManager->flush();

            return $this->redirectToRoute('backoffice_checkpoint_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/checkpoint/new.html.twig', [
            'checkpoint' => $checkpoint,
            'form' => $form,
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{id}", name="backoffice_checkpoint_show", methods={"GET"})
     */
    public function show(Checkpoint $checkpoint): Response
    {
    
        $game = $checkpoint->getGame();

        return $this->render('backoffice/checkpoint/show.html.twig', [
            'checkpoint' => $checkpoint,
            'game' => $game,
            
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="backoffice_checkpoint_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Checkpoint $checkpoint, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CheckpointType::class, $checkpoint);
        $form->handleRequest($request);
        $game = $checkpoint->getGame();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('backoffice_checkpoint_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/checkpoint/edit.html.twig', [
            'checkpoint' => $checkpoint,
            'form' => $form,
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{id}", name="backoffice_checkpoint_delete", methods={"POST"})
     */
    public function delete(Request $request, Checkpoint $checkpoint, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$checkpoint->getId(), $request->request->get('_token'))) {
            $entityManager->remove($checkpoint);
            $entityManager->flush();
        }

        return $this->redirectToRoute('backoffice_checkpoint_index', [], Response::HTTP_SEE_OTHER);
    }
}
