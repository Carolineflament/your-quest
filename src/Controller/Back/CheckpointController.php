<?php

namespace App\Controller\Back;

use App\Entity\Checkpoint;
use App\Form\CheckpointType;
use App\Repository\CheckpointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/checkpoint")
 */
class CheckpointController extends AbstractController
{
    /**
     * @Route("/", name="back_checkpoint_index", methods={"GET"})
     */
    public function index(CheckpointRepository $checkpointRepository): Response
    {
        return $this->render('back/checkpoint/index.html.twig', [
            'checkpoints' => $checkpointRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="back_checkpoint_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $checkpoint = new Checkpoint();
        $form = $this->createForm(CheckpointType::class, $checkpoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($checkpoint);
            $entityManager->flush();

            return $this->redirectToRoute('back_checkpoint_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/checkpoint/new.html.twig', [
            'checkpoint' => $checkpoint,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="back_checkpoint_show", methods={"GET"})
     */
    public function show(Checkpoint $checkpoint): Response
    {
        return $this->render('back/checkpoint/show.html.twig', [
            'checkpoint' => $checkpoint,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="back_checkpoint_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Checkpoint $checkpoint, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CheckpointType::class, $checkpoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_checkpoint_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/checkpoint/edit.html.twig', [
            'checkpoint' => $checkpoint,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="back_checkpoint_delete", methods={"POST"})
     */
    public function delete(Request $request, Checkpoint $checkpoint, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$checkpoint->getId(), $request->request->get('_token'))) {
            $entityManager->remove($checkpoint);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_checkpoint_index', [], Response::HTTP_SEE_OTHER);
    }
}
