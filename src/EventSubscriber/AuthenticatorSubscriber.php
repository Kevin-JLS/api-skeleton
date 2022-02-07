<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticatorSubscriber implements EventSubscriberInterface
{

    private LoggerInterface $securityLogger;

    private RequestStack $requestStack;

    public function __construct(
        LoggerInterface $securityLogger,
        RequestStack $requestStack
    )
    {
        $this->securityLogger = $securityLogger;
        $this->requestStack = $requestStack;
    }

    /** @return array<string> */

    public static function getSubscribedEvents()
    {
        return [
            'security.authentication.failure'                   => 'onSecurityAuthenticationFailure',
            'security.authentication.success'                   => 'onSecurityAuthenticationSuccess',
            SecurityEvents::INTERACTIVE_LOGIN                   => 'onSecurityInteractiveLogin',
            'Symfony\Component\Security\Http\Event\LogoutEvent' => 'onSecurityLogout',
            'security.logout_on_change'                         => 'onSecurityLogoutOnChange',
            SecurityEvents::SWITCH_USER                         => 'onSecuritySwitchUser'
        ];
    }

    public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event): void
    {

        ['user_IP' => $userIP] = $this->getRouteNameAndUserIP();

        /** @var TokenInterface $securityToken */
        $securityToken = $event->getAuthenticationToken();

        ['email' => $emailEntered] = $securityToken->getCredentials();

        $this->securityLogger->info("Un utilisateur ayant ladresse IP '{$userIP}' a tenté de s'authentifier sans succés avec l'email suivant : '{$emailEntered}' ");
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {

        [
            'route_name' => $routeName,
            'user_IP' => $userIP
        ] = $this->getRouteNameAndUserIP();

        if (empty($event->getAuthenticationToken()->getRoleNames())) {
            $this->securityLogger->info("Oh, un utilisateur anonyme ayant l'adresse IP '{$userIP}' est apparu sur la route '{$routeName}' ");
        } else {
            /** @var TokenInterface $securityToken */
            $securityToken = $event->getAuthenticationToken();

            $userEmail = $this->getUserEmail($securityToken);

            $this->securityLogger->info("Un utilisateur anonyme ayant l'adresse IP '{$userIP}' a évolué en entité User avec l'email '{$userEmail}'.");
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        ['user_IP' => $userIP] = $this->getRouteNameAndUserIP();

        /** @var TokenInterface $securityToken */
        $securityToken = $event->getAuthenticationToken();

        $userEmail = $this->getUserEmail($securityToken);

        $this->securityLogger->info("Un utilisateur anonyme ayant l'adresse IP '{$userIP}' a évolué en entité User avec l'email '{$userEmail}'.");

    }

    public function onSecurityLogout(LogoutEvent $event): void
    {
        /** @var RedirectResponse|null $response */
        $response = $event->getResponse();

        /** @var TokenInterface|null $securityToken */
        $securityToken = $event->getToken();

        if (!$response || !$securityToken) {
            return;
        }

        ['user_IP' => $userIP] = $this->getRouteNameAndUserIP();

        $userEmail = $this->getUserEmail($securityToken);

        $targetUrl = $response->getTargetUrl();

        $this->securityLogger->info("L'utilisateur ayant l'adresse IP '{$userIP}' s'est déconnecté et a été redirigé vers l'url suivante : '{$targetUrl}'.");
    }

    public function onSecurityLogoutOnChange(TokenDeauthenticatedEvent $event): void
    {
        // ...
    }

    public function onSecuritySwitchUser(SwitchUserEvent $event): void
    {
        // ...
    }

    /**
     * Return the user IP and the name of the route where the user has arrived.
     * 
     * @return array{user_IP: string|null, route_name:mixed}
     */
    private function getRouteNameAndUserIP(): array 
    {
        $request = $this->requestStack->getCurrentRequest(); 

        if(!$request) {
            return [
                'user_IP'       => 'Inconnue',         
                'route_name'    => 'Inconnue'         
            ];
        }

        return [
            'user_IP'       => $request->getClientIp() ?? 'Inconnue',         
            'route_name'    => $request->attributes->get('_route')         
        ];
    }

    private function getUserEmail(TokenInterface $securityToken): string 
    {
        /** @var User $user */
        $user = $securityToken->getUser();

        return $user->getEmail();
    }
}