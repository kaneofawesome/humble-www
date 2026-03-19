<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountLockChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isLocked()) {
            throw new CustomUserMessageAccountStatusException(
                'Your account has been locked due to policy violations. Please contact support to have it reinstated.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
