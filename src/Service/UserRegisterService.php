<?php
// src/Service/UserRegisterService.php

namespace App\Service;

use App\Document\User;
use App\Repository\UserRepository;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;


class UserRegisterService
{
    private UserRepository $userRepository;
    private PasswordEncoder $passwordEncoder;
    private DocumentManager $documentManager;

    public function __construct(PasswordEncoder $passwordEncoder, DocumentManager $documentManager)
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->passwordEncoder = $passwordEncoder;
        $this->documentManager = $documentManager;
    }

    public function registerUser(string $username, string $email, string $password, string $verifyToken): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($this->passwordEncoder->encodePassword($password));
        $user->setDateCreated(new \DateTime());
        $user->setVerifyToken($verifyToken);
        $user->setActive(false);


        $this->documentManager->persist($user);
        $this->documentManager->flush();

        return $user;
    }
}