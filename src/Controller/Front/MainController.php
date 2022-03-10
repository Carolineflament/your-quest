<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MainController extends AbstractController
{    
    /**
     * @Route("/", name="front_main", methods={"GET"})
     */
    public function index(GameRepository $gameRepository, Request $request): Response
    {    
        $games = $gameRepository->findBy(['isTrashed' => 0, 'status' => 1], ['createdAt' => 'ASC']);

        return $this->render('front/main/index.html.twig', [
            'games' => $games,
        ]);
    }
}
