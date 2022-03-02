<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use App\Repository\RoundRepository;
use App\Repository\ScanQRRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstanceController extends AbstractController
{
    /**
     * @Route("/jeu/{gameSlug}/instance/{instanceSlug}", name="app_front_instance_show", methods={"GET"})
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
     * @Route("/jeu/{gameSlug}/instance/{instanceSlug}/realtime", name="app_front_instance_realtime", methods={"GET"})
     */
    public function realtime($gameSlug, $instanceSlug, InstanceRepository $instanceRepository, RoundRepository $roundRepository, ScanQRRepository $scanQRRepository): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Get parent Game
        $game = $instance->getGame();

        /***** Avancée des joueurs en temps réél *****/

        // Je crée un tableau des checkpoints, et chaque checkpoint contiendra un tableau de joueurs étant localisés à ce checkpoint
        $checkpointsArray = [];

        // Je récupére la liste des checkpoints du jeu (dans l'ordre)
        $checkpointsList = $game->getCheckpoints();
        

        // Je crée un sous tableau pour chaque checkpoint, et je l'insère dans le le tableau général
        foreach ($checkpointsList as $checkpoint) {
            $checkpointsArray[$checkpoint->getId()] = [];
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
            $checkpointsArray[$lastCheckpoint->getId()] = $round->getUser();
        }

        dd($checkpointsArray);

        return $this->render('front/instance/realtime.html.twig', [
            'instance' => $instance,
            'game' => $game,
            'checkpointsDatas' => $checkpointsArray,
        ]);
    }

}   
