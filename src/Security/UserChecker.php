<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserChecker implements UserCheckerInterface 
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

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

        if($user->getIsGuardCheckIp() && !$this->isUserIpIsInWhiteList($user))
        {
            throw new CustomUserMessageAccountStatusException("Vous n'êtes pas autorisé à vous authentifier avec cette adresse IP car elle ne figure pas dans la liste blanche des adresses IP autorisées !");
        }
    }

    private function isUserIpIsInWhiteList(User $user): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if(!$request) {
            return false;
        }

        $userIP = $request->getClientIp();

        $userWhiteListIp = $user->getWhitelistedIpAddresses();

        return in_array($userIP, $userWhiteListIp, true);
    }
}