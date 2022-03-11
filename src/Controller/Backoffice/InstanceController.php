<?php

namespace App\Controller\Backoffice;

use App\Entity\Instance;
use App\Form\InstanceType;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use App\Repository\RoundRepository;
use DateTimeImmutable;
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

        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game);

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

        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game);

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

            return $this->redirectToRoute('app_backoffice_game_show', ['slug' => $gameSlug], Response::HTTP_SEE_OTHER);
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
        $this->denyAccessUnlessGranted('IS_MY_GAME', $instance);

        // Get parent Game
        $game = $instance->getGame();

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
        $this->denyAccessUnlessGranted('IS_MY_GAME', $instance);

        // Get parent Game
        $game = $instance->getGame();

        $form = $this->createForm(InstanceType::class, $instance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Message
            $this->addFlash(
                'notice-success',
                'L\'instance '.$instance->getTitle().' a bien été éditée !'
            );

            return $this->redirectToRoute('app_backoffice_game_show', ['slug' => $game->getSlug()], Response::HTTP_SEE_OTHER);
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
        $this->denyAccessUnlessGranted('IS_MY_GAME', $instance);

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

        return $this->redirectToRoute('app_backoffice_game_show', ['slug' => $game->getSlug()], Response::HTTP_SEE_OTHER);
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

        // Now
        $now = new DateTimeImmutable();

        // Is instance not started yet ?
        if ($now < $instance->getStartAt()) {

            // Redirect to Instance show
            // + flash message
            $this->addFlash(
                'notice-danger',
                'Cette instance n\'a pas encore débuté, impossible d\'afficher le classement des joueurs pour l\'instant.'
            );
            return $this->redirectToRoute('app_backoffice_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()], Response::HTTP_SEE_OTHER);
            
        }

        // Is instance is active now
        if ($now > $instance->getStartAt() && $now < $instance->getEndAt()) {

            // Redirect to realtime page
            return $this->redirectToRoute('app_front_instance_realtime', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()], Response::HTTP_SEE_OTHER);
            
        }

        // Je récupére la liste des rounds terminés et non-terminés d'une instance
        $roundsList = $roundRepository->findBy(['instance' => $instance]);

        // Pour chaque round terminé, je calcule la durée de celui-ci, et je l'inscris dans un tableau
        $DurationsArray = [];

        foreach ($roundsList as $key => $round) {
            // Work only on finished rounds (endAt is not null)
            if ($round->getEndAt()) {

                // Convert to seconds
                $timestampEndAt = $round->getEndAt()->getTimestamp();
                $timestampStartAt = $round->getStartAt()->getTimestamp();

                // Duration in seconds
                $roundDuration = $timestampEndAt - $timestampStartAt;
                
                // Send to array
                $DurationsArray[$key] = $roundDuration;
            }
        }

        // Je tri le tableau des durées dans l'ordre ASC
        asort($DurationsArray);

        // Je crée un nouveau tableau où je transorme les durées en secondes en J-H-M-S
        $formatedDurationsArray = [];

        foreach ($DurationsArray as $round => $duration) {
            if ($duration < 3600) {
                $heures = 0;
                
                if ($duration < 60) {
                    $minutes = 0;
                } else {
                    $minutes = round($duration / 60);
                }
                
                $secondes = floor($duration % 60);
            } else {
                $heures = round($duration / 3600);
                $secondes = round($duration % 3600);
                $minutes = floor($secondes / 60);
            }
                
            $secondes2 = round($secondes % 60);
               
            $formatedDuration  = "$heures heures $minutes min $secondes2 sec";

            // J'envoie au tableau
            $formatedDurationsArray[$round] = $formatedDuration;
        }

        // Datas for breadcrumb
        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $instance->getTitle(), 'libelle_url' => 'app_backoffice_instance_show', 'url' => $this->urlGenerator->generate('app_backoffice_instance_show', ['instanceSlug' => $instance->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => 'score', 'libelle_url' => 'app_backoffice_instance_score', 'url' => $this->urlGenerator->generate('app_backoffice_instance_score', ['instanceSlug' => $instance->getSlug()])));


        return $this->render('backoffice/instance/score.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'roundsList' => $roundsList,
            'orderedDurations' => $formatedDurationsArray,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }
}
