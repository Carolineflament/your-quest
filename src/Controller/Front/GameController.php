<?php

namespace App\Controller\Front;

use App\Entity\Game;
use App\Entity\Instance;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/jeux",name="front_")
 */
class GameController extends AbstractController
{
    private $paramBag;
    private $urlGenerator;
    private $breadcrumb;

    public function __construct(ParameterBagInterface $paramBag, UrlGeneratorInterface $urlGenerator)
    {
        $this->paramBag = $paramBag;
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Accueil', 'libelle_url' => 'front_main', 'url' => $this->urlGenerator->generate('front_main')));
    }
    /**
     * @Route("/", name="games", methods={"GET"})
     */
    public function index(GameRepository $gameRepository, Request $request): Response
    {
        $limit_games_per_page = $this->paramBag->get('app.limit_games_per_page');
        $current_page = $request->query->get('page') ? $request->query->get('page') : 1;
        
        $games = $gameRepository->findBy(['isTrashed' => 0, 'status' => 1], ['createdAt' => 'DESC'], $limit_games_per_page, ($current_page-1)*$limit_games_per_page);

        array_push($this->breadcrumb, array('libelle' => 'Les jeux', 'libelle_url' => 'front_games', 'url' => $this->urlGenerator->generate('front_games')));

        return $this->render('front/game/index.html.twig', [
            'games' => $games,
            "pages" => ceil(count($gameRepository->findBy(['isTrashed' => 0, 'status' => 1]))/$limit_games_per_page),
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{slug}",name="games_show", methods={"GET"})
     *
     * @return Response
     */
    public function show(Game $game): Response
    {
        /* This is a method of the Game entity that returns all the instances of the game that are not
        trashed. */
        $instances = $game->getUnTrashedInstances();
        /* This is a method of the Instance entity that returns all the instances that are not trashed
        and that are not finished. */
        $next_instances = $instances->filter(function(Instance $instance){
            return $instance->getEndAt() > new \DateTimeImmutable();
        });
        /* This is a method of the Instance entity that returns all the instances that are not trashed
        and that are finished. */
        $previous_instances = $instances->filter(function(Instance $instance){
            return $instance->getEndAt() <= new \DateTimeImmutable();
        });

        array_push($this->breadcrumb, array('libelle' => 'Les jeux', 'libelle_url' => 'front_games', 'url' => $this->urlGenerator->generate('front_games')));
        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'front_games_show', 'url' => $this->urlGenerator->generate('front_games_show', ['slug' => $game->getSlug()])));

        return $this->render('front/game/show.html.twig', [
            'game' => $game,
            'next_instances' => $next_instances,
            'previous_instances' => $previous_instances,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }
}
