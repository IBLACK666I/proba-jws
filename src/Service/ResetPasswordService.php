<?php

namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Repository\UserRepository;
use App\Security\Encoder\PasswordEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordService
{
    private UserRepository $userRepository;
    private PasswordEncoder $passwordEncoder;


    public function __construct(
        PasswordEncoder                  $passwordEncoder,
        private readonly DocumentManager $documentManager,
        private UserValidator            $userValidator
    )
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->passwordEncoder = $passwordEncoder;
    }

    public function resetPassword(string $token, string $password, string $password_confirmation): JsonResponse
    {
        $user = $this->userRepository->findOneByToken($token);
        if (!$this->userValidator->validatePassword($password)) {
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }
        if (!$user) {
            return new JsonResponse(['message' => 'check the link'], Response::HTTP_NOT_FOUND);
        }
        if ($password != $password_confirmation) {
            return new JsonResponse(['message' => 'Passwords must match.'], Response::HTTP_BAD_REQUEST);
        }
        $expdat = $user->getResetTokenExpiry();
        if ($expdat < new \DateTime()) {
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
        } else {
            $user->setPassword($this->passwordEncoder->encodePassword($password));
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
        }
        return new JsonResponse(['message' => 'Password reset successfully.'], Response::HTTP_OK);
    }
}