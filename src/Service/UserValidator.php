<?php
// src/Service/UserValidator.php

namespace App\Service;
use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Jcupitt\Vips\Image;
class UserValidator
{
    private UserRepository $userRepository;

    public function __construct(DocumentManager $documentManager)
    {
        $this->userRepository = $documentManager->getRepository(User::class);
    }

    public function isUsernameAvailable(string $username): bool
    {
            return null === $this->userRepository->findOneByUsername($username);
  }
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function validatePassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password);
    }


}