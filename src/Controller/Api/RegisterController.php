<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
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
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DocumentManager $documentManager,
    ) {}

    #[Route('/register')]
    public function index(Request $request, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
        $password = $requestData['password'] ?? null;
        $email = trim(  $requestData['email'] ?? '');
        $email_lower = strtolower( $email );
        $username_lower = strtolower( $username );

        $user = $this->userRepository->findOneByUsername($username_lower);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'message' => 'Invalid email address',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password)) {
            return new JsonResponse([
                'message' => 'Password must be at least 10 characters long and contain at least one lowercase letter, one uppercase letter, one digit, and one special character',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (null !== $user) {
            return new JsonResponse([
                'message' => 'Username is already taken',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($username_lower);
        $user->setEmail($email_lower);
        $user->setPassword($passwordEncoder->encodePassword($password));
        $user->setDateCreated(new \DateTime());
        $user->setActive(true);
        $user->setLastLogin(new \DateTime());
        $user->setLastActive(new \DateTime());

        $this->documentManager->persist($user);
        $this->documentManager->flush();

        // TODO 1. create seperate services with all of the above logic, and pass it to the method by Dependency Injection (UserValidator, UserRegisterService)
        // TODO 2. send welcome email (with another service)?
        return new JsonResponse([
            'message' => 'User crated successfully',
        ], Response::HTTP_CREATED);
    }
}