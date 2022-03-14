<?php

namespace App\Controller\Front;

use App\Entity\Round;
use App\Repository\CheckpointRepository;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use App\Repository\RoundRepository;
use App\Repository\ScanQRRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InstanceController extends AbstractController
{

    private $breadcrumb;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Acceuil', 'libelle_url' => 'front_main', 'url' => $this->urlGenerator->generate('front_main')));
    }

    /**
     * @Route("/jeux/{gameSlug}/instances/{instanceSlug}", name="app_front_instance_show", methods={"GET"})
     */
    public function show($gameSlug, $instanceSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository): Response
    {
        // Get Game from slug
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Datas for breadcrumb
        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'front_games_show', 'url' => $this->urlGenerator->generate('front_games_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $instance->getTitle(), 'libelle_url' => 'app_front_instance_show', 'url' => $this->urlGenerator->generate('app_front_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()])));

        return $this->render('front/instance/show.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

     /**
     * @Route("/jeux/{gameSlug}/instances/{instanceSlug}/score", name="app_front_instance_score", methods={"GET"})
     */
    public function score($gameSlug, $instanceSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository, RoundRepository $roundRepository): Response
    {
        // Get Game from slug
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

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
            return $this->redirectToRoute('app_front_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()], Response::HTTP_SEE_OTHER);
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
        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'front_games_show', 'url' => $this->urlGenerator->generate('front_games_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $instance->getTitle(), 'libelle_url' => 'app_front_instance_show', 'url' => $this->urlGenerator->generate('app_front_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => 'score', 'libelle_url' => 'app_front_instance_score', 'url' => $this->urlGenerator->generate('app_front_instance_score', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()])));

        return $this->render('front/instance/score.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'roundsList' => $roundsList,
            'orderedDurations' => $formatedDurationsArray,
            'breadcrumbs' => $this->breadcrumb,
        ]);

    }



    /**
     * @Route("/jeux/{gameSlug}/instances/{instanceSlug}/realtime", name="app_front_instance_realtime", methods={"GET"})
     */
    public function realtime($gameSlug, $instanceSlug, InstanceRepository $instanceRepository, CheckpointRepository $checkpointRepository, RoundRepository $roundRepository, ScanQRRepository $scanQRRepository): Response
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
                'Cette instance n\'a pas encore débuté, impossible d\'afficher la position des joueurs en temps réel.'
            );
            return $this->redirectToRoute('app_front_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()], Response::HTTP_SEE_OTHER);
            
        }

        // Is instance finished
        if ($now > $instance->getEndAt()) {

            // Redirect to score page
            // + flash message
            $this->addFlash(
                'notice-danger',
                'Cette instance est terminée, impossible d\'afficher la position des joueurs en temps réel, mais voici le classement final.'
            );
            return $this->redirectToRoute('app_front_instance_score', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()], Response::HTTP_SEE_OTHER);
        }

        /***** Avancée des joueurs en temps réél *****/

        // Je crée un tableau associatif vide pour les checkpoints du jeu, et chaque checkpoint contiendra un tableau des joueurs ayant comme dernière position ce checkpoint
        // key = checkpoint.title
        // value = array des joueurs
        $checkpointsArray = [];

        // Je récupére la liste des checkpoints du jeu (not trashed, et dans l'ordre défini par l'organisateur)
        $checkpointsList = $checkpointRepository->findBy(['game' => $game,'isTrashed' => false], ['orderCheckpoint' => 'ASC']);
        

        // Je crée un sous tableau vide pour chaque checkpoint, et je l'insère dans le le tableau général
        foreach ($checkpointsList as $checkpoint) {
            $checkpointsArray[$checkpoint->getTitle()] = [];
        }
        
        // Je récupère tous les rounds de l'instance
        $instanceRounds = $instance->getRounds();
        
        // Je boucle sur les rounds de l'instance
        foreach ($instanceRounds as $round) {

            // Je récupère le dernier scanQR de ce round
            $lastScanQR = $scanQRRepository->findOneBy(
                ['round' => $round],
                ['scanAt'=> 'DESC']                        
            );
            
            if($lastScanQR !== null)
            {
                // Je récupère le checkpoint de ce dernier scan
                $lastCheckpoint = $lastScanQR->getCheckpoint();
            
                // Au tableau général, j'inscris le joueur du round dans le tableau de joueur de ce checkpoint
                $checkpointsArray[$lastCheckpoint->getTitle()][] = $round->getUser();
            }
        }

        // Datas for breadcrumb
        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'front_games_show', 'url' => $this->urlGenerator->generate('front_games_show', ['slug' => $game->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => $instance->getTitle(), 'libelle_url' => 'app_front_instance_show', 'url' => $this->urlGenerator->generate('app_front_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()])));

        array_push($this->breadcrumb, array('libelle' => 'score', 'libelle_url' => 'app_front_instance_realtime', 'url' => $this->urlGenerator->generate('app_front_instance_realtime', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()])));
        
        return $this->render('front/instance/realtime.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'checkpointsDatas' => $checkpointsArray,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

}   