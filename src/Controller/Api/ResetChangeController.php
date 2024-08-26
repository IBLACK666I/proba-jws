<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Security\Encoder\PasswordEncoder;
use App\Service\UserRegisterService;
use App\Service\ResetPasswordService;
use App\Service\UserValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetChangeController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private UserRegisterService      $userRegisterService,
        private ResetPasswordService     $resetPasswordService,
        private UserValidator            $userValidator)
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/api/reset-password/{token}', name: 'app_reset_resetpassword')]
    public function resetPassword(Request $request, string $token, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $password = $requestData['password'] ?? null;
        $password_confirmation = $requestData['password2'] ?? null;
        $user = $this->userRepository->findOneByToken($token);
        if (!$user) {
            return new JsonResponse(['message' => 'check the link'], Response::HTTP_NOT_FOUND);
        }
        if ($password != $password_confirmation) {
            return new JsonResponse(['message' => 'Passwords must match.'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->userValidator->validatePassword($password)) {
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }
        $this->resetPasswordService->resetPassword($token, $password, $user);
        return new JsonResponse(['message' => 'Password reset successfully.'], Response::HTTP_OK);
    }
}