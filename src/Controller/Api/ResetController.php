<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\EmailAndDataService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ResetController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager     $documentManager,
        private readonly EmailAndDataService $emailAndDataService)
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/api/request-reset-password')]
    public function requestResetPassword(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
        return $this->emailAndDataService->sendPassResetEmail($username);
    }
}