<?php
namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class ResetPasswordService
{
    private DocumentManager $documentManager;
    private UserValidator $userValidator;
    private PasswordEncoder $passwordEncoder;

    public function __construct(DocumentManager $documentManager, UserValidator $userValidator, PasswordEncoder $passwordEncoder)
    {
        $this->documentManager = $documentManager;
        $this->userValidator = $userValidator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function resetPassword(string $token, string $password, string $passwordConfirmation): JsonResponse
    {
        if ($password != $passwordConfirmation) {
            return new JsonResponse(['message' => 'Passwords must match.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->userValidator->validatePassword($password)) {
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->documentManager->getRepository(User::class)->findOneByToken($token);

        if ($user === null) {
            return new JsonResponse(['message' => 'Invalid reset token get a new reset request'], Response::HTTP_NOT_FOUND);
        } else {
            $expdat = $user->getResetTokenExpiry();
            if ($expdat < new \DateTime()) {
                $user->setResetToken(null);
                $user->setResetTokenExpiry(null);
                $this->documentManager->flush();
                return new JsonResponse(['message' => 'Token timed out', 'time' => new \DateTime()], Response::HTTP_BAD_REQUEST);
            } else {
                $user->setPassword($this->passwordEncoder->encodePassword($password));
                $user->setResetToken(null);
                $user->setResetTokenExpiry(null);
                $this->documentManager->flush();
                return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);
            }
        }
    }
}