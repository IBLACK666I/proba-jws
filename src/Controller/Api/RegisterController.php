<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\EmailAndDataService;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Jcupitt\Vips\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private EmailAndDataService      $emailAndDataService)
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('api/register')]
    public function index(Request $request, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $email = trim($requestData['email'] ?? '');
        $password = $requestData['password'] ?? null;
        return $this->emailAndDataService->sendVerifyEmail($email, $password);
    }
}