<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\EmailAndDataService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegisterVerifyController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private EmailAndDataService      $emailAndDataService)
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/api/verify/{token}', name: 'app_verify')]
    public function registerVerify(Request $request, string $token): JsonResponse
    {
        return $this->emailAndDataService->ActivateUser($token);
    }
}