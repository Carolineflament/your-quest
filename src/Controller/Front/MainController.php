<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

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

    /**
     * @Route("/message", name="front_message", methods={"GET"})
     */
    public function message(GameRepository $gameRepository, Request $request): Response
    {    
        $games = $gameRepository->findBy(['isTrashed' => 0, 'status' => 1], ['createdAt' => 'ASC']);
       

        if ($this->getUser() == null) {
            /* Adding a flash message */
            $this->addFlash(
                "notice-danger",
                "Il faut vour inscrire comme organisateur pour pouvoir créer un jeu, vous pouvez remplir ce formulaire !"
            );

            return $this->redirectToRoute('app_register');

        } else if ($this->getUser() !== null && in_array("ROLE_JOUEUR", $this->getUser()->getRoles())) {
            /* It's adding a flash message */
            $this->addFlash(
                "notice-danger",
                "Il faut vour inscrire comme organisateur pour pouvoir créer un jeu ! Pour cela vous pouvez nous contacter à l'adresse admin@yourquest.fr"
            );

            return $this->redirectToRoute('front_main');

        } else if ($this->getUser() !== null && in_array("ROLE_ORGANISATEUR", $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_backoffice_game_new');
        }        
    }
}
