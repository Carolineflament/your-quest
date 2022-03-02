<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/jeux",name="front_")
 */
class GameController extends AbstractController
{
    private const LIMIT_GAMES_PER_PAGE = 5;
    /**
     * @Route("/", name="games")
     */
    public function index(GameRepository $gameRepository, Request $request): Response
    {
        $current_page = $request->query->get('page');
        $games = $gameRepository->findBy(['isTrashed' => 0, 'status' => 1], ['createdAt' => 'ASC'], self::LIMIT_GAMES_PER_PAGE, ($current_page-1)*self::LIMIT_GAMES_PER_PAGE);

        return $this->render('front/game/index.html.twig', [
            'games' => $games,
            "pages" => ceil(count($gameRepository->findAll())/self::LIMIT_GAMES_PER_PAGE)
        ]);
    }

    /**
     * @Route("/{slug}",name="games_show", methods={"GET"})
     *
     * @return Response
     */
    public function show(): Response
    {
        return $this->render('front/game/show.html.twig', [
        ]);
    }
}
