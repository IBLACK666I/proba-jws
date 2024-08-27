<?php

namespace App\Service;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ActivateUserService
{
    private UserRepository $userRepository;
    private DocumentManager $documentManager;

    public function __construct( DocumentManager $documentManager)
    {
        $this->userRepository = $documentManager->getRepository(User::class);
    }

    public function ActivateUser(string $token): JsonResponse
    {
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