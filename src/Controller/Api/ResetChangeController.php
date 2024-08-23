<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Service\UserValidator;
use App\Repository\UserRepository;
use App\Security\Encoder\PasswordEncoder;
use App\Service\UserRegisterService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetChangeController extends AbstractController
{
    private UserRepository $userRepository;
    private UserValidator $userValidator;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private UserRegisterService      $userRegisterService
    )
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/api/reset-password/{token}', name: 'app_reset_resetpassword')]
    public function resetPassword(Request $request, string $token, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $password = $requestData['password'] ?? null;
        $password_confirmation = $requestData['password2'] ?? null;
        if ($password != $password_confirmation) {
            return new JsonResponse(['message' => 'Passwords must match.'], Response::HTTP_BAD_REQUEST);
        }
        if(!$this->userValidator->validatePassword($password)){
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneByToken($token);

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
                $user->setPassword($passwordEncoder->encodePassword($password));
                $user->setResetToken(null);
                $user->setResetTokenExpiry(null);
                $this->documentManager->flush();
                return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);

            }
        }
    }
}
