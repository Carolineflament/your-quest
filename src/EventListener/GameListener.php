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

    public function updateSlug(Game $game)
    {
        //slug
        $slug = $this->slugger->slugify($game->getTitle(), Game::class);
        // update slug in the entity
        $game->setSlug($slug);
    }
}