<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CheckpointVoter extends Voter
{
    private $security;

    public function __construct (Security $security)
    {
        // We find security Object in dependancy injection
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['EDIT_CHECKPOINT', 'VIEW_CHECKPOINT', 'DELETE_CHECKPOINT']);
    }

    protected function voteOnAttribute(string $attribute, $checkpoint, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check conditions and return true to grant permission
        switch ($attribute) {
            case "EDIT_CHECKPOINT":
            case "VIEW_CHECKPOINT":
            case "DELETE_CHECKPOINT":
            {
                //Admin can modify this game
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }
                if ($user->getId() === $checkpoint->getGame()->getUser()->getId() && $this->security->isGranted('ROLE_ORGANISATEUR')) {
                    return true;
                }
                break;
            }
        }

        return false;
    }
}
