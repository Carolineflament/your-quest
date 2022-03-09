<?php

namespace App\Controller\Backoffice;

use App\Entity\Enigma;
use App\Form\EnigmaType;
use App\Repository\CheckpointRepository;
use App\Repository\EnigmaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/question")
 */
class EnigmaController extends AbstractController
{
    /**
     * @Route("/checkpoint/{id}", name="app_backoffice_enigma_index", methods={"GET"})
     */
    public function index($id,EnigmaRepository $enigmaRepository, CheckpointRepository $checkpointRepository): Response
    {
        $checkpoint = $checkpointRepository->findOneBy(['id' => $id]);

        return $this->render('backoffice/enigma/index.html.twig', [
            'enigmas' => $enigmaRepository->findBy(['checkpoint' => $checkpoint, 'isTrashed' => false]),
            'checkpoint' => $checkpoint,
        ]);
    }

    /**
     * @Route("/checkpoint/{id}/nouveau", name="app_backoffice_enigma_new", methods={"GET", "POST"})
     */
    public function new($id,Request $request,EntityManagerInterface $entityManager, CheckpointRepository $checkpointRepository): Response
    {
        $checkpoint = $checkpointRepository->findOneBy(['id' => $id]);

        $enigma = new Enigma();
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        $enigma->setCheckpoint($checkpoint);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enigma);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'L\'énigme a été ajouté !');

            return $this->redirectToRoute('app_backoffice_enigma_index', ['id' => $checkpoint->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/enigma/new.html.twig', [
            'enigma' => $enigma,
            'form' => $form,
            'checkpoint' => $checkpoint,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_enigma_show", methods={"GET"})
     */
    public function show(Enigma $enigma): Response
    {
        $checkpoint = $enigma->getCheckpoint();
        return $this->render('backoffice/enigma/show.html.twig', [
            'enigma' => $enigma,
            'checkpoint' => $checkpoint,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="app_backoffice_enigma_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Enigma $enigma, EntityManagerInterface $entityManager): Response
    {
        $checkpoint = $enigma->getCheckpoint();
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'L\'énigme a été modifié !');

            return $this->redirectToRoute('app_backoffice_enigma_index', [
                'id' => $checkpoint->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/enigma/edit.html.twig', [
            'enigma' => $enigma,
            'form' => $form,
            'checkpoint' => $checkpoint,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_enigma_delete", methods={"POST"})
     */
    public function delete(Request $request, Enigma $enigma, EntityManagerInterface $entityManager): Response
    {
        $checkpoint = $enigma->getCheckpoint();
        if ($this->isCsrfTokenValid('delete'.$enigma->getId(), $request->request->get('_token'))) {
            $entityManager->remove($enigma);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_enigma_index', [
            'id' => $checkpoint->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
