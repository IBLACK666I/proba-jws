<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\ResetEmailService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;


class ResetController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager   $documentManager,
        private readonly ResetEmailService $resetEmailService,
    )
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/api/request-reset-password')]
    public function requestResetPassword(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
        return $this->resetEmailService->sendPassResetEmail($username);
    }
}