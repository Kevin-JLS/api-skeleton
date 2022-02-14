<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private BruteForceChecker $bruteForceChecker;
    private UrlGeneratorInterface $urlGenerator;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        BruteForceChecker $bruteForceChecker,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->bruteForceChecker = $bruteForceChecker;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );

        sleep(1);

        if($endOfBlackListing = $this->bruteForceChecker->getEndOfBlackListing()) {
            throw new CustomUserMessageAccountStatusException("Il semblerait que vous avez oublié vos identifiants. Par mesure de sécurité, vous devez patienter jusqu'à {$endOfBlackListing} avant de tenter une nouvelle connexion. Vous pouvez cliquer sur le lien ci-dessus pour effectuer une demande de modification de votre mot de passe.");
        }
    }

    // // Rajouter
    // /** @return array<string> */
    // public function getCredentials(Request $request): array
    // {
    //     $credentials = [
    //         'email' => $request->request->get('email'),
    //         'password' => $request->request->get('password'),
    //         'csrf_token' => $request->request->get('_csrf_token')
    //     ];
    //     $request->getSession()->set(
    //         Security::LAST_USERNAME,
    //         $credentials['email']
    //     );

    //     return $credentials;
    // }

    
    // public function getUser($credentials, UserProviderInterface $userProvider): User
    // {
    //     sleep(1);

    //     if($endOfBlackListing = $this->bruteForceChecker->getEndOfBlackListing()) {
    //         throw new CustomUserMessageAccountStatusException("Il semblerait que vous avez oublié vos identifiants. Par mesure de sécurité, vous devez patienter jusqu'à {$endOfBlackListing} avant de tenter une nouvelle connexion. Vous pouvez cliquer sur le lien ci-dessus pour effectuer une demande de modification de votre mot de passe.");
    //     }

    //     // Rajouter
    //     $token = new CsrfToken('authenticate', $credentials['csrf_token']);

    //     if(!$this->csrfTokenManager->isTokenValid($token)) {
    //         throw new InvalidCsrfTokenException();
    //     }

    //     $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

    //     if (!$user) {
    //         // fail authentication with a custom error
    //         throw new CustomUserMessageAuthenticationException('Identifiants invalides.');

    //         return $user;
    //     }
        
    // }

    // Rajouter
    // public function checkCredentials($credentials, UserInterface $user): bool 
    // {
    //     return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    // }


    // Rajouter
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    // public function getPassword($credentials): ?string
    // {
    //     return $credentials['password'];
    // }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));

    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

}
