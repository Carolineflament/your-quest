<?php

namespace App\Security\Voter;

use App\Entity\Answer;
use App\Entity\Checkpoint;
use App\Entity\Enigma;
use App\Entity\Game;
use App\Entity\Instance;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GameVoter extends Voter
{
    private $security;

    public function __construct (Security $security)
    {
        // We find security Object in dependancy injection
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['IS_MY_GAME']);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if($subject instanceof Answer)
        {
            $game = $subject->getEnigma()->getCheckpoint()->getGame();
        }
        elseif($subject instanceof Enigma)
        {
            $game = $subject->getCheckpoint()->getGame();
        }
        elseif($subject instanceof Checkpoint || $subject instanceof Instance)
        {
            $game = $subject->getGame();
        }
        elseif($subject instanceof Game)
        {
            $game = $subject;
        }
        
        // Check conditions and return true to grant permission
        switch ($attribute) {
            case "IS_MY_GAME":
            {
                //Admin can modify this game
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                if ($user->getId() === $game->getUser()->getId() && $this->security->isGranted('ROLE_ORGANISATEUR')) {
                    return true;
                }
                break;
            }
        }

        return false;
    }
}
