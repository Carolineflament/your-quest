<?php

namespace App\Controller\Backoffice;

use App\Entity\Checkpoint;
use App\Entity\Game;
use App\Form\CheckpointType;
use App\Repository\CheckpointRepository;
use App\Repository\GameRepository;
use App\Service\CascadeTrashed;
use App\Service\QrcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/back/checkpoint", name="app_backoffice_checkpoint_")
 */
class CheckpointController extends AbstractController
{
    private $breadcrumb;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Jeux', 'libelle_url' => 'app_backoffice_game_index', 'url' => $this->urlGenerator->generate('app_backoffice_game_index')));
    }

    /**
     * @Route("/jeux/{gameSlug}", name="index", methods={"GET"})
     */
    public function index($gameSlug, GameRepository $gameRepository, CheckpointRepository $checkpointRepository): Response
    {
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        return $this->render('backoffice/checkpoint/index.html.twig', [
            'checkpoints' => $checkpointRepository->findBy(['game' => $game,'isTrashed' => false]),
            'game' =>$game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/jeux/{gameSlug}/nouveau", name="new", methods={"GET", "POST"})
     */
    public function new($gameSlug, GameRepository $gameRepository, Request $request, EntityManagerInterface $entityManager,QrcodeService $qrcodeService): Response
    {
        $checkpoint = new Checkpoint();
        $form = $this->createForm(CheckpointType::class, $checkpoint);
        $form->handleRequest($request);

        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        $checkpoint->setGame($game);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($checkpoint);
            $entityManager->flush();

            $qrcodeService->qrcode($checkpoint);

            $this->addFlash(
                'notice-success',
                'Le checkpoint '.$checkpoint->getTitle().' a été ajouté !'
            );

            return $this->redirectToRoute('app_backoffice_checkpoint_index', [
                'gameSlug' => $game->getSlug()
            ], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => 'Nouveau checkpoint', 'libelle_url' => 'app_backoffice_checkpoint_new', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_new', ['gameSlug' => $game->getSlug()])));

        return $this->renderForm('backoffice/checkpoint/new.html.twig', [
            'checkpoint' => $checkpoint,
            'form' => $form,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Checkpoint $checkpoint): Response
    {
        $game = $checkpoint->getGame();

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $checkpoint->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $checkpoint->getId()])));

        return $this->render('backoffice/checkpoint/show.html.twig', [
            'checkpoint' => $checkpoint,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, Checkpoint $checkpoint, EntityManagerInterface $entityManager, QrcodeService $qrcodeService): Response
    {
        $game = $checkpoint->getGame();
        $form = $this->createForm(CheckpointType::class, $checkpoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();

            $qrcodeService->qrcode($checkpoint);

            $this->addFlash(
                'notice-success',
                'Le checkpoint '.$checkpoint->getTitle().' a été modifié !'
            );

            return $this->redirectToRoute('app_backoffice_checkpoint_index', [
                'gameSlug' => $game->getSlug()
            ], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $checkpoint->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_edit', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_edit', ['id' => $checkpoint->getId()])));

        return $this->renderForm('backoffice/checkpoint/edit.html.twig', [
            'checkpoint' => $checkpoint,
            'form' => $form,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="trash", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function trash(Request $request, Checkpoint $checkpoint, EntityManagerInterface $entityManager, CascadeTrashed $cascadeTrashed): Response
    {
        $game = $checkpoint->getGame();

        if ($this->isCsrfTokenValid('delete'.$checkpoint->getId(), $request->request->get('_token'))) {
            if ($checkpoint->getIsTrashed()) {
                $cascadeTrashed->trashCheckpoint($checkpoint);
            }
            $checkpoint->setIsTrashed(true);
            $this->addFlash(
                'notice-success',
                'Le checkpoint '.$checkpoint->getTitle().' a été supprimé ! Le checkpoint et ses énigmes ont été mis à la poubelle !'
            );
        }
            
            $entityManager->flush();
            
        
            return $this->redirectToRoute('app_backoffice_checkpoint_index', [
            'gameSlug' => $game->getSlug()
        ], Response::HTTP_SEE_OTHER);
        }
    }
