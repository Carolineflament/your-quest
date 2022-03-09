<?php

namespace App\Controller\Backoffice;

use App\Entity\Instance;
use App\Form\InstanceType;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use App\Repository\RoundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/back/instances", name="app_backoffice_instance_")
 */
class InstanceController extends AbstractController
{
    private $breadcrumb;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Jeux', 'libelle_url' => 'app_backoffice_game_index', 'url' => $this->urlGenerator->generate('app_backoffice_game_index')));
    }
    
    /**
     * List all instances that belong to game = {gameSlug}
     * @Route("/jeux/{gameSlug}", name="index", methods={"GET"})
     */
    public function index($gameSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository): Response
    {
        // Get parent Game
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        return $this->render('backoffice/instance/index.html.twig', [
            'instances' => $instanceRepository->findBy(['game' => $game, 'isTrashed' => false]),
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * Create new instance that belongs to game = {gameId}
     * @Route("/jeux/{gameSlug}/nouveau", name="new", methods={"GET", "POST"})
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

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => 'Nouvelle instance', 'libelle_url' => 'app_backoffice_instance_new', 'url' => $this->urlGenerator->generate('app_backoffice_instance_new', ['gameSlug' => $game->getSlug()])));

        return $this->renderForm('backoffice/instance/new.html.twig', [
            'instance' => $instance,
            'form' => $form,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * 
     * @Route("/{instanceSlug}", name="show", methods={"GET"})
     */
    public function show($instanceSlug, InstanceRepository $instanceRepository): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('VIEW_INSTANCE', $instance);

        // Get parent Game
        $game = $instance->getGame();
       

        // TODO Vérifier que l'utilisateur en cours est le propriétaire du jeu

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $instance->getTitle(), 'libelle_url' => 'app_backoffice_instance_show', 'url' => $this->urlGenerator->generate('app_backoffice_instance_show', ['instanceSlug' => $instance->getSlug()])));

        return $this->render('backoffice/instance/show.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{instanceSlug}/modifier", name="edit", methods={"GET", "POST"})
     */
    public function edit($instanceSlug, Request $request, InstanceRepository $instanceRepository, EntityManagerInterface $entityManager): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('EDIT_INSTANCE', $instance);

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

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $instance->getTitle(), 'libelle_url' => 'app_backoffice_instance_edit', 'url' => $this->urlGenerator->generate('app_backoffice_instance_edit', ['instanceSlug' => $instance->getSlug()])));

        return $this->renderForm('backoffice/instance/edit.html.twig', [
            'instance' => $instance,
            'form' => $form,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="trash", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Request $request, Instance $instance, EntityManagerInterface $entityManager): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('DELETE_INSTANCE', $instance);

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

    /**
     * @Route("/{instanceSlug}/score", name="score", methods={"GET"})
     */
    public function score($instanceSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository, RoundRepository $roundRepository): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Get parent Game
        $game = $instance->getGame();

        // Je récupére la liste des rounds terminés et non-terminés d'une instance
        $roundsList = $roundRepository->findBy(['instance' => $instance]);

        // Pour chaque round je calcul la durée de celui-ci, et je l'inscrit dans un tableau
        $DurationsArray = [];

        foreach ($roundsList as $key => $round) {
            // if endAt is not null
            if ($round->getEndAt()) {
            
                $roundDuration = $round->getEndAt()->diff($round->getStartAt());
                
                dd($roundDuration->format('%h'));
                $DurationsArray[$key] = $roundDuration;
            }
        }

        
        // Je tri le tableau des durées par clé (durée du round)
        asort($DurationsArray);
        dd($DurationsArray);

        

        return $this->render('front/instance/score.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'roundslist' => $roundsList,

        ]);
    }
}
