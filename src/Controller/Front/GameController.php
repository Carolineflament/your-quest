<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/games", name="games")
     */
    public function index(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findAll();
        return $this->render('front/game/index.html.twig', [
            'games' => $games
        ]);
    }
}
