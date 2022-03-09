<?php

namespace App\Controller\Backoffice;

use App\Entity\Enigma;
use App\Form\EnigmaType;
use App\Repository\EnigmaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backoffice/enigma")
 */
class EnigmaController extends AbstractController
{
    /**
     * @Route("/", name="app_backoffice_enigma_index", methods={"GET"})
     */
    public function index(EnigmaRepository $enigmaRepository): Response
    {
        return $this->render('backoffice/enigma/index.html.twig', [
            'enigmas' => $enigmaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_backoffice_enigma_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $enigma = new Enigma();
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enigma);
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_enigma_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/enigma/new.html.twig', [
            'enigma' => $enigma,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_enigma_show", methods={"GET"})
     */
    public function show(Enigma $enigma): Response
    {
        return $this->render('backoffice/enigma/show.html.twig', [
            'enigma' => $enigma,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_backoffice_enigma_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Enigma $enigma, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_enigma_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/enigma/edit.html.twig', [
            'enigma' => $enigma,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_enigma_delete", methods={"POST"})
     */
    public function delete(Request $request, Enigma $enigma, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$enigma->getId(), $request->request->get('_token'))) {
            $entityManager->remove($enigma);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_enigma_index', [], Response::HTTP_SEE_OTHER);
    }
}
