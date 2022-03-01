<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
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
    public function realtime($gameSlug, $instanceSlug, InstanceRepository $instanceRepository): Response
    {
        // Get Instance from slug
        $instance = $instanceRepository->findOneBy(['slug' => $instanceSlug]);

        // Get parent Game
        $game = $instance->getGame();

        return $this->render('front/instance/realtime.html.twig', [
            'instance' => $instance,
            'game' => $game,
        ]);
    }

}   
