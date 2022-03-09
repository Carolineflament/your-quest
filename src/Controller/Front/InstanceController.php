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

class InstanceController extends AbstractController
{
    /**
     * @Route("/jeux/{gameSlug}/instances/{instanceSlug}", name="app_front_instance_show", methods={"GET"})
     */
    public function show($gameSlug, $instanceSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository): Response
    {
        // Get Game from slug
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);


        return $this->render('front/instance/show.html.twig', [
            'instance' => $instance,
            'game' => $game,
        ]);
    }

     /**
     * @Route("/jeu/{gameSlug}/instance/{instanceSlug}/scores", name="app_front_instance_score", methods={"GET"})
     */
    public function score($gameSlug, $instanceSlug, GameRepository $gameRepository, InstanceRepository $instanceRepository, RoundRepository $roundRepository): Response
    {
        // Get Game from slug
        $game = $gameRepository->findOneBy(['slug' => $gameSlug]);

        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // je récupéré la liste des round d'une instance

        /* This is a query to get all the rounds of an instance. */
        $roundsList = $roundRepository->findBy(['instance' => $instance]);

        return $this->render('front/instance/score.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'roundsList' => $roundsList

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
                'Cette instance n\'a pas encore débuté, impossible d\'afficher la position des joueurs.'
            );
            return $this->redirectToRoute('app_front_instance_show', ['gameSlug' => $game->getSlug(), 'instanceSlug' => $instance->getSlug()], Response::HTTP_SEE_OTHER);
            
        }

        // Is instance finished
        if ($now > $instance->getEndAt()) {

            // Redirect to score page
            // + flash message
            $this->addFlash(
                'notice-danger',
                'Cette instance est terminée, impossible d\'afficher la position des joueurs en tant réél, mais voici le tableau des scores.'
            );
            // TODO redirect to score page
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

            // Je récupère le checkpoint de ce dernier scan
            $lastCheckpoint = $lastScanQR->getCheckpoint();

            // Au tableau général, j'inscris le joueur du round dans le tableau de joueur de ce checkpoint
            $checkpointsArray[$lastCheckpoint->getTitle()][] = $round->getUser();
        }
        
        return $this->render('front/instance/realtime.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'checkpointsDatas' => $checkpointsArray,
        ]);
    }

}   
