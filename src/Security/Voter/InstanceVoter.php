<?php

namespace App\Security\Voter;

use DateTimeImmutable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InstanceVoter extends Voter 
{
    protected function supports(string $attribute, $subject): bool
    {
        // si l'attribut commence par MOVIE_ alors on veut voter
        if ($attribute === "INSTANCE_IS_ACTIVE")
        {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, $instance, TokenInterface $token): bool
    {
        // Now
        $now = new DateTimeImmutable();

        // Is instance active ?
        if ($now < $instance->getStartAt() || $now > $instance->getEndAt()) {
            return false;
        }

        return true;
    }

}