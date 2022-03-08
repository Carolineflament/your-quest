<?php

namespace App\Controller\Backoffice;

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
 * @Route("/back/instances")
 */
class InstanceController extends AbstractController
{
    /**
     * List all instances that belong to game = {gameSlug}
     * @Route("/jeux/{gameSlug}", name="app_backoffice_instance_index", methods={"GET"})
     */
    public function index($gameSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository): Response
    {
        // Get parent Game
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        return $this->render('backoffice/instance/index.html.twig', [
            'instances' => $instanceRepository->findBy(['game' => $game, 'isTrashed' => false]),
            'game' => $game,
        ]);
    }

    /**
     * Create new instance that belongs to game = {gameId}
     * @Route("/jeux/{gameSlug}/nouveau", name="app_backoffice_instance_new", methods={"GET", "POST"})
     */
    public function new($gameSlug, GameRepository $gameRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get parent Game
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        $instance = new Instance();
        $form = $this->createForm(InstanceType::class, $instance);
        $form->handleRequest($request);

        // Set Instance game property
        $instance->setGame($game);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($instance);
            $entityManager->flush();

            // Message
            $this->addFlash(
                'notice-success',
                'L\'instance '.$instance->getTitle().' a bien été créée !'
            );

            return $this->redirectToRoute('app_backoffice_instance_index', ['gameSlug' => $gameSlug], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/instance/new.html.twig', [
            'instance' => $instance,
            'form' => $form,
            'game' => $game,
        ]);
    }

    /**
     * 
     * @Route("/{instanceSlug}", name="app_backoffice_instance_show", methods={"GET"})
     */
    public function show($instanceSlug, InstanceRepository $instanceRepository): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Get parent Game
        $game = $instance->getGame();
       

        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        return $this->render('backoffice/instance/show.html.twig', [
            'instance' => $instance,
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{instanceSlug}/modifier", name="app_backoffice_instance_edit", methods={"GET", "POST"})
     */
    public function edit($instanceSlug, Request $request, InstanceRepository $instanceRepository, EntityManagerInterface $entityManager): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Get parent Game
         $game = $instance->getGame();

        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        $form = $this->createForm(InstanceType::class, $instance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Message
            $this->addFlash(
                'notice-success',
                'L\'instance '.$instance->getTitle().' a bien été éditée !'
            );

            return $this->redirectToRoute('app_backoffice_instance_index', ['gameSlug' => $game->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/instance/edit.html.twig', [
            'instance' => $instance,
            'form' => $form,
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_instance_trash", methods={"POST"})
     */
    public function delete(Request $request, Instance $instance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$instance->getId(), $request->request->get('_token'))) {
            $instance->setIsTrashed(true);
            $entityManager->flush();

            // Message
            $this->addFlash(
                'notice-success',
                'L\'instance '.$instance->getTitle().' a bien été supprimée !'
            );
        }

        // Get parent Game
        $game = $instance->getGame();


        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        return $this->redirectToRoute('app_backoffice_instance_index', ['gameSlug' => $game->getSlug()], Response::HTTP_SEE_OTHER);
    }
}
