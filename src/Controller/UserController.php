<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\User;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Jcupitt\Vips\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {}

    #[Route('/users/create')]
    public function index(PasswordEncoder $passwordEncoder): JsonResponse
    {
        $username = 'mateusz.pietka@codelabs.pl';

        $user = $this->documentManager->getRepository(User::class)->findOneBy([
            'username' => $username,
        ]);

        // Create a new user if one does not already exist.
        if (null === $user) {
            $user = new User();
            $user->setFirstname('Mateusz');
            $user->setUsername($username);
            $user->setPassword($passwordEncoder->encodePassword('Password123!'));

            // Set new Object Id
            $this->documentManager->persist($user);
            $this->documentManager->flush();
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'username' => $user->getUsername(),
        ], Response::HTTP_CREATED);
    }
}
