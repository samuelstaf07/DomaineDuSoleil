<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof \App\Entity\Users) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte est inactif.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {

    }
}
