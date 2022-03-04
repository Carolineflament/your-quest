<?php

namespace App\Security\Voter;

use App\Entity\Game;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GameVoter extends Voter
{
    private $games;

    public function __construct (Security $security)
    {
        // We find security Object in dependancy injection
        $this->security = $security;
    }

    protected function supports(string $attribute, $game): bool
    {
        return in_array($attribute, ["VIEW_GAME"])
        && $game instanceof Game;
    }

    protected function voteOnAttribute(string $attribute, $game, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check conditions and return true to grant permission
        switch ($attribute) {
            case "VIEW_GAME":
                //Organizer of this game, or Admin
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                if ($user === $game->getUser()) {
                    return true;
                }
                break;
            // case self::VIEW:
            //     return true;
            //     break;
        }

        return false;
    }
}
