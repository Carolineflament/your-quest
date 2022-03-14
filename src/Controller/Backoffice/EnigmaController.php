<?php

namespace App\Controller\Backoffice;

use App\Entity\Checkpoint;
use App\Entity\Enigma;
use App\Form\EnigmaType;
use App\Repository\CheckpointRepository;
use App\Repository\EnigmaRepository;
use App\Repository\GameRepository;
use App\Service\CascadeTrashed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/back/question", name="app_backoffice_enigma_")
 */
class EnigmaController extends AbstractController
{
    private $breadcrumb;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Jeux', 'libelle_url' => 'app_backoffice_game_index', 'url' => $this->urlGenerator->generate('app_backoffice_game_index')));
    }

    /**
     * @Route("/checkpoint/{id}", name="index", methods={"GET"})
     */
    public function index($id,Checkpoint $checkpoint, EnigmaRepository $enigmaRepository, CheckpointRepository $checkpointRepository): Response
    {
        $checkpoint = $checkpointRepository->findOneBy(['id' => $id]);
        $game = $checkpoint->getGame();


        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $checkpoint->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $checkpoint->getId()])));

       array_push($this->breadcrumb, array('libelle' => 'Enigme' , 'libelle_url' => 'app_backoffice_enigma_index', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_index', ['id' => $checkpoint->getId()])));

        return $this->render('backoffice/enigma/index.html.twig', [
            'enigmas' => $enigmaRepository->findBy(['checkpoint' => $checkpoint, 'isTrashed' => false]),
            'checkpoint' => $checkpoint,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/checkpoint/{id}/nouveau", name="new", methods={"GET", "POST"})
     */
    public function new($id,Request $request,EntityManagerInterface $entityManager, CheckpointRepository $checkpointRepository): Response
    {
        $checkpoint = $checkpointRepository->findOneBy(['id' => $id]);

        $game = $checkpoint->getGame();

        $enigma = new Enigma();
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        $enigma->setCheckpoint($checkpoint);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enigma);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'L\'énigme a été ajoutée !');

            return $this->redirectToRoute('app_backoffice_checkpoint_show', ['id' => $checkpoint->getId()], Response::HTTP_SEE_OTHER);
        }
        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $checkpoint->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $checkpoint->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Nouvelle énigme', 'libelle_url' => 'app_backoffice_enigma_new', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_new', ['id' => $checkpoint->getId()])));

        return $this->renderForm('backoffice/enigma/new.html.twig', [
            'enigma' => $enigma,
            'form' => $form,
            'game' => $game,
            'checkpoint' => $checkpoint,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Enigma $enigma): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $enigma);
        $checkpoint = $enigma->getCheckpoint();

        $game = $checkpoint->getGame();

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $checkpoint->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $checkpoint->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Enigme n°'.$enigma->getOrderEnigma(), 'libelle_url' => 'app_backoffice_enigma_show', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_show', ['id' => $enigma->getId()])));

        return $this->render('backoffice/enigma/show.html.twig', [
            'enigma' => $enigma,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Enigma $enigma, EntityManagerInterface $entityManager): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $enigma);
        $checkpoint = $enigma->getCheckpoint();

        $game = $checkpoint->getGame();

        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'L\'énigme a été modifiée !');
            
            return $this->redirectToRoute('app_backoffice_checkpoint_show', [
                'id' => $checkpoint->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $checkpoint->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $checkpoint->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Enigme n°'. $enigma->getOrderEnigma(), 'libelle_url' => 'app_backoffice_enigma_edit', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_edit', ['id' => $checkpoint->getId()])));

        return $this->renderForm('backoffice/enigma/edit.html.twig', [
            'enigma' => $enigma,
            'form' => $form,
            'checkpoint' => $checkpoint,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="trash", methods={"POST"})
     */
    public function trash(Request $request, Enigma $enigma, EntityManagerInterface $entityManager, CascadeTrashed $cascadeTrashed): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $enigma);
        $checkpoint = $enigma->getCheckpoint();
        if ($this->isCsrfTokenValid('delete'.$enigma->getId(), $request->request->get('_token')))
        {
            $cascadeTrashed->trashEnigma($enigma);
            $this->addFlash(
                'notice-success',
                'L\' énigme a été mise à la poubelle !'
            );
        }
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de supprimer l\'énigme '.$enigma->getOrderEnigma().', token invalide !'
            );
        }

        return $this->redirectToRoute('app_backoffice_checkpoint_show', [
            'id' => $checkpoint->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
