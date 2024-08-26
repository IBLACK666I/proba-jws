<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\UserRegisterService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class RegisterVerifyController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private UserRegisterService      $userRegisterService
    ){
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }
    #[Route('/api/verify/{token}', name: 'app_verify')]
    public function registerVerify(Request $request, string $token): JsonResponse {
        $user = $this->userRepository->findOneByTokenVerify($token);
        if ($user === null) {
            return new JsonResponse(['message' => 'Check link'], Response::HTTP_NOT_FOUND);
        } else {
            $user->setActive(true);
            $user->setVerifyToken(null);
            $user->setLastActive(new \DateTime());
            $this->documentManager->flush();
            return new JsonResponse(['message' => 'Account is now created and verified '], Response::HTTP_OK);
        }
    }
}