<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\UserRegisterService;
use App\Service\UserValidator;
use App\Service\VerifyEmailService;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Jcupitt\Vips\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class RegisterController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private UserRegisterService $userRegisterService,
        private UserValidator $userValidator,
        private VerifyEmailService $verifyEmailService
    ) {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('api/register')]
    public function index(Request $request, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $email = trim(  $requestData['email'] ?? '');
        $password = $requestData['password'] ?? null;
        $username=strtolower($email);
        $user = $this->userRepository->findOneByUsername($username);
        if(!$this->userValidator->isUsernameAvailable($username)) {
            return new JsonResponse(['message' => 'Email is not available'], Response::HTTP_BAD_REQUEST);
        }
        if(!$this->userValidator->validateEmail($email)) {
            return new JsonResponse(['message' => 'Email is not valid'], Response::HTTP_BAD_REQUEST);
        }
        if(!$this->userValidator->validatePassword($password)){
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }
        $verifyToken = Uuid::v4()->toRfc4122();
        $user = $this->userRegisterService->registerUser($username, $email, $password, $verifyToken);
        $verifyLink = $this->generateUrl('app_verify', ['token' => $verifyToken], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->verifyEmailService->sendVeifyEmail($email,$verifyLink);
        return new JsonResponse(['message' => 'Verification email was sent'], Response::HTTP_CREATED);
    }
}