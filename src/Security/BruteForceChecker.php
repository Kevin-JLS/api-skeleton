<?php 

namespace App\Security;

use App\Repository\AuthLogRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class BruteForceChecker
{
    private AuthLogRepository $authLogRepository;
    private RequestStack $requestStack;

    public function __construct(AuthLogRepository $authLogRepository, RequestStack $requestStack)
    {
        $this->authLogRepository = $authLogRepository;
        $this->requestStack = $requestStack;
    }
    
    /**
     * Adds a failed authentication attempt and adds a blacklisting according to the number of failed attempts.
     *
     * @param  string $emailEntered
     * @param  string $userIP
     * @return void
     */
    public function addFailedAuthAttempt(string $emailEntered, ?string $userIP): void 
    {
        if($this->authLogRepository->isBlackListedWithThisAttemptFailure($emailEntered, $userIP)){
            $this->authLogRepository->addFailedAuthAttempt($emailEntered, $userIP, true);
        } else {
            $this->authLogRepository->addFailedAuthAttempt($emailEntered, $userIP);       
        }
    }
    
    /**
     * Returns the end of blacklisting rounded up to the next minutes or null
     *
     * @return string|null Example: if the end of blacklisting is 12:01:37 it returns 12h02
     */
    public function getEndOfBlackListing(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if(!$request) {
            return null;
        }

        $userIP = $request->getClientIp();

        $emailEntered = $request->request->get('email');

        return $this->authLogRepository->getEndOfBlackListing($emailEntered, $userIP);
    }
}