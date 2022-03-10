<?php

namespace App\EventListener;

use App\Entity\Game;
use App\Service\MySlugger;

class GameListener
{
    private $slugger;

    public function __construct(MySlugger $slugger)
    {
        $this->slugger = $slugger;    
    }

    public function createSlug(Game $game)
    {
        //slug
        $slug = $this->slugger->slugifyCreate($game->getTitle(), Game::class);
        // update slug in the game
        $game->setSlug($slug);
    }

    public function updateSlug(Game $game)
    {
        //slug
        $slug = $this->slugger->slugifyUpdate($game->getTitle(), Game::class, $game->getId());
        // update slug in the game
        $game->setSlug($slug);
    }
}