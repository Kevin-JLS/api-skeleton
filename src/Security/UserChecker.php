<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserChecker implements UserCheckerInterface 
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        /* Warning, if you enter a wrong password, the exception will be displayed. */
        if(!$user->getIsVerified()) {
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas actif, veuillez consulter vos e-mails pour l'activer avant le {$user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H\hi')}");
        }
    }
}