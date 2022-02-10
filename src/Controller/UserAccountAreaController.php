<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 *   @Route("/user/account/profile/", name="app_user_account_profile_")
 */
class UserAccountAreaController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    } 

    /**
     * @Route("home", name="home", methods={"GET})
     */
    public function home(ArticleRepository $articleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user_account_area/index.html.twig', [
            'user' => $user,
            'articlesCreatedCount'  => $articleRepository->getCountOfArticlesCreated($user),
            'articlesPublished'     => $articleRepository->getCountOfArticlesPublished($user),
            'controller_name'       => 'UserAccountAreaController'
        ]);
    }

        
    /**
     * @Route("add-IP", name="add-IP", methods={"GET"})
     *
     */
    public function addUserIPToWhiteList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if(!$request->isXmlHttpRequest()) {
            throw new HttpException(400, 'The header "X-Requested-With" is a missing');
        }

        $userIP = $request->getClientIp();

        /** @var User $user */
        $user = $this->getUser();

        $user->setWhitelistedIpAddresses($userIP);

        $this->entityManager->flush();

        return $this->json([
            'message' => "Adresse IP ajoutée à la liste blanche",
            'user_IP' => $userIP
        ]);
    }

    /**
     * @Route("toogle-checking-ip", name="toggle_checking_IP", methods={"POST"})
     */
    public function toogleGuardCheckingIP(Request $request): JsonResponse
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        if(!$request->isXmlHttpRequest()) {
            throw new HttpException(400, 'The header "X-Requested-With" is a missing');
        }

        $switchValue = $request->getContent();

        if(!in_array($switchValue, ['true', 'false'], true)) {
            throw new HttpException(400, "Expected value is 'true'or 'false'");        
        }

        /** @var User $user */
        $user = $this->getUser();

        $isSwitchON = filter_var($switchValue, FILTER_VALIDATE_BOOLEAN);

        $user->setIsGuardCheckIp($isSwitchON);

        $this->entityManager->flush();

        return $this->json([
            'isGuardCheckingIP' => $isSwitchON
        ]);

    }

    
}
