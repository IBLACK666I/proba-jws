<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\UserRegisterService;
use App\Service\UserValidator;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Jcupitt\Vips\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private UserRegisterService $userRegisterService,
        private UserValidator $userValidator
    ) {
        $this->userRepository = $this->documentManager->getRepository(User::class);
        $this->userValidator = $userValidator;
        $this->userRegisterService=$userRegisterService;
    }

    #[Route('/register')]
    public function index(Request $request, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $email = trim(  $requestData['email'] ?? '');
        $password = $requestData['password'] ?? null;
        $username=strtolower($email);

        $user = $this->userRepository->findOneByUsername($username);
        if(!$this->userValidator->isUsernameAvailable($username)) {
            return new JsonResponse(['message' => 'Username is not available'], Response::HTTP_BAD_REQUEST);
        }
        if(!$this->userValidator->validateEmail($email)) {
            return new JsonResponse(['message' => 'Email is not valid'], Response::HTTP_BAD_REQUEST);
        }
        if(!$this->userValidator->validatePassword($password)){
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRegisterService->registerUser($username, $email, $password);
        // TODO 1. create seperate services with all of the above logic, and pass it to the method by Dependency Injection (UserValidator, UserRegisterService)
        // TODO 2. send welcome email (with another service)?
        return new JsonResponse([
            'message' => 'User crated successfully',
        ], Response::HTTP_CREATED);
    }
}