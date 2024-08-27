<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\ResetPasswordService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ResetChangeController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private ResetPasswordService     $resetPasswordService)
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }
    #[Route('/api/reset-password/{token}', name: 'app_reset_resetpassword')]
    public function resetPassword(Request $request, string $token): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $password = $requestData['password'] ?? null;
        $password_confirmation = $requestData['password2'] ?? null;
        return $this->resetPasswordService->resetPassword($token, $password, $password_confirmation);
    }
}