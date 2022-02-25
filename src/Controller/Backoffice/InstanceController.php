<?php

namespace App\Controller\Backoffice;

use App\Entity\Game;
use App\Entity\Instance;
use App\Form\InstanceType;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/instance")
 */
class InstanceController extends AbstractController
{
    /**
     * List all instances that belong to game = {gameSlug}
     * @Route("/jeu/{gameSlug}", name="app_backoffice_instance_index", methods={"GET"})
     */
    public function index($gameSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository): Response
    {
        // Get parent Game
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        return $this->render('backoffice/instance/index.html.twig', [
            'instances' => $instanceRepository->findBy(['game' => $game]),
            'game' => $game,
        ]);
    }

    /**
     * Create new instance that belongs to game = {gameId}
     * @Route("/jeu/{gameSlug}/nouveau", name="app_backoffice_instance_new", methods={"GET", "POST"})
     */
    public function new($gameSlug, GameRepository $gameRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $instance = new Instance();
        $form = $this->createForm(InstanceType::class, $instance);
        $form->handleRequest($request);

        // Get parent Game
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // Set Instance game property
        $instance->setGame($game);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($instance);
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_instance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/instance/new.html.twig', [
            'instance' => $instance,
            'form' => $form,
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_instance_show", methods={"GET"})
     */
    public function show(Instance $instance): Response
    {
        return $this->render('backoffice/instance/show.html.twig', [
            'instance' => $instance,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="app_backoffice_instance_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Instance $instance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InstanceType::class, $instance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backoffice_instance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/instance/edit.html.twig', [
            'instance' => $instance,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_instance_delete", methods={"POST"})
     */
    public function delete(Request $request, Instance $instance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$instance->getId(), $request->request->get('_token'))) {
            $entityManager->remove($instance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_instance_index', [], Response::HTTP_SEE_OTHER);
    }
}
