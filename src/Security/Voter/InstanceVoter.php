<?php

namespace App\Security\Voter;

use DateTimeImmutable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class InstanceVoter extends Voter 
{
    private $security;

    public function __construct (Security $security)
    {
        // We find security Object in dependancy injection
        $this->security = $security;
    }
    
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ["INSTANCE_IS_ACTIVE", 'EDIT_INSTANCE', 'VIEW_INSTANCE', 'DELETE_INSTANCE']);

        return false;
    }

    protected function voteOnAttribute(string $attribute, $instance, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check conditions and return true to grant permission
        switch ($attribute) {
            case "EDIT_INSTANCE":
            case "VIEW_INSTANCE":
            case "DELETE_INSTANCE":
            {
                //Admin can modify this game
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }
                if ($user->getId() === $instance->getGame()->getUser()->getId() && $this->security->isGranted('ROLE_ORGANISATEUR')) {
                    return true;
                }
                break;
            }
            case "INSTANCE_IS_ACTIVE":
            {
                // Now
                $now = new DateTimeImmutable();

                // Is instance active ?
                if ($now > $instance->getStartAt() && $now < $instance->getEndAt()) {
                    return true;
                }
                break;
            }
        }

        return false;
    }

}