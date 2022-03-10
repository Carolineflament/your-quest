<?php

namespace App\Controller\Backoffice;

use App\Entity\Answer;
use App\Entity\Enigma;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/backoffice/answer", name="app_backoffice_answer_")
 */
class AnswerController extends AbstractController
{
    private $breadcrumb;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Jeux', 'libelle_url' => 'app_backoffice_game_index', 'url' => $this->urlGenerator->generate('app_backoffice_game_index')));
    }

    /**
     * @Route("/enigme/{id}", name="index", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function index(Enigma $enigma, AnswerRepository $answerRepository): Response
    {
        array_push($this->breadcrumb, array('libelle' => $enigma->getCheckpoint()->getGame()->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $enigma->getCheckpoint()->getGame()->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $enigma->getCheckpoint()->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $enigma->getCheckpoint()->getId()])));

        return $this->render('backoffice/answer/index.html.twig', [
            'answers' => $answerRepository->findBy(['enigma' => $enigma, 'isTrashed' => false]),
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/enigme/{id}/nouveau", name="new", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, Enigma $enigma): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answer->setEnigma($enigma);
            $entityManager->persist($answer);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'La réponse '.$answer->getAnswer().' a été créé !'
            );

            return $this->redirectToRoute('app_backoffice_enigma_show', ['id' => $enigma->getId()], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => $enigma->getCheckpoint()->getGame()->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $enigma->getCheckpoint()->getGame()->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $enigma->getCheckpoint()->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $enigma->getCheckpoint()->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Enigme'.$enigma->getOrderEnigma(), 'libelle_url' => 'app_backoffice_enigma_show', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_show', ['id' => $enigma->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Nouvelle réponse', 'libelle_url' => 'app_backoffice_answer_new', 'url' => $this->urlGenerator->generate('app_backoffice_answer_new', ['id' => $enigma->getId()])));

        return $this->renderForm('backoffice/answer/new.html.twig', [
            'answer' => $answer,
            'form' => $form,
            'enigma' => $enigma,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(Answer $answer): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $answer);

        array_push($this->breadcrumb, array('libelle' => $answer->getEnigma()->getCheckpoint()->getGame()->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $answer->getEnigma()->getCheckpoint()->getGame()->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $answer->getEnigma()->getCheckpoint()->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $answer->getEnigma()->getCheckpoint()->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Enigme '.$answer->getEnigma()->getOrderEnigma(), 'libelle_url' => 'app_backoffice_enigma_index', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_show', ['id' => $answer->getEnigma()->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Réponse : '.$answer->getAnswer(), 'libelle_url' => 'app_backoffice_answer_show', 'url' => $this->urlGenerator->generate('app_backoffice_answer_show', ['id' => $answer->getId()])));

        return $this->render('backoffice/answer/show.html.twig', [
            'answer' => $answer,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $answer);

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'La réponse '.$answer->getAnswer().' a été modifié !'
            );

            return $this->redirectToRoute('app_backoffice_answer_index', [], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => $answer->getEnigma()->getCheckpoint()->getGame()->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $answer->getEnigma()->getCheckpoint()->getGame()->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $answer->getEnigma()->getCheckpoint()->getTitle(), 'libelle_url' => 'app_backoffice_checkpoint_show', 'url' => $this->urlGenerator->generate('app_backoffice_checkpoint_show', ['id' => $answer->getEnigma()->getCheckpoint()->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Enigme '.$answer->getEnigma()->getOrderEnigma(), 'libelle_url' => 'app_backoffice_enigma_index', 'url' => $this->urlGenerator->generate('app_backoffice_enigma_show', ['id' => $answer->getEnigma()->getId()])));

        array_push($this->breadcrumb, array('libelle' => 'Réponse : '.$answer->getAnswer(), 'libelle_url' => 'app_backoffice_answer_edit', 'url' => $this->urlGenerator->generate('app_backoffice_answer_edit', ['id' => $answer->getId()])));

        return $this->renderForm('backoffice/answer/edit.html.twig', [
            'answer' => $answer,
            'form' => $form,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="trash", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function trash(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$answer->getId(), $request->request->get('_token'))) {

            $answer->setIsTrashed(true);
            $this->addFlash(
                'notice-success',
                'La réponse '.$answer->getAnswer().' a été supprimé !'
            );
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backoffice_answer_index', [], Response::HTTP_SEE_OTHER);
    }
}
