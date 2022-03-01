<?php

namespace App\Service;

use App\Entity\Checkpoint;
use App\Entity\Enigma;
use App\Entity\Game;
use App\Entity\Instance;
use Doctrine\ORM\EntityManagerInterface;

class CascadeTrashed
{
    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Put in trash a game and all is checkpoint and his instance
     *
     * @param Game $game
     * @return void
     */
    public function trashGame(Game $game)
    {
        $game->setIsTrashed(true);
        foreach($game->getCheckpoints() AS $checkpoint)
        {
            $this->trashCheckpoint($checkpoint);
        }
        foreach($game->getInstances() AS $instance)
        {
            $this->trashInstance($instance);
        }
        $this->doctrine->flush();
    }

    /**
     * Put in trash an instance
     *
     * @param Checkpoint $checkpoint
     * @return void
     */
    public function trashInstance(Instance $instance)
    {
        $instance->setIsTrashed(true);
        $this->doctrine->flush();
    }

    /**
     * Put in trash a checkpoint and all is enigma
     *
     * @param Checkpoint $checkpoint
     * @return void
     */
    public function trashCheckpoint(Checkpoint $checkpoint)
    {
        $checkpoint->setIsTrashed(true);
        foreach($checkpoint->getEnigmas() AS $enigma)
        {
            $this->trashEnigma($enigma);
        }
        $this->doctrine->flush();
    }

    /**
     * Put in trash an enigma
     *
     * @param Enigma $enigma
     * @return void
     */
    public function trashEnigma(Enigma $enigma)
    {
        $enigma->setIsTrashed(true);
        $this->doctrine->flush();
    }
}