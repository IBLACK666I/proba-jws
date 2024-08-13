<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
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
        private readonly DocumentManager $documentManager
    ) {}

    #[Route('/register')]
    public function index(Request $request, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
        $password = $requestData['password'] ?? null;
        $email = trim($requestData['email'] ?? '');

        // TODO search just by username, with strtolower($email) function
        $user = $this->documentManager->getRepository(User::class)->findOneBy([
            '$or' => [
                ['username' => $username],
                ['email' => $email],
            ],
        ]);

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
            // TODO change the message, it's a security issue, suggesting to someone that the user already exists in the database
            return new JsonResponse([
                'message' => 'User already exists',
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        // TODO Save username always with strtolower
        $user->setUsername($username);
        $user->setEmail($email);
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
