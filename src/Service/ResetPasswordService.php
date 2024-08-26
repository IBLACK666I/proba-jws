<?php

namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Repository\UserRepository;
use App\Security\Encoder\PasswordEncoder;

class ResetPasswordService
{
    private UserRepository $userRepository;
    private PasswordEncoder $passwordEncoder;
    private DocumentManager $documentManager;

    public function __construct(
        PasswordEncoder $passwordEncoder,
        DocumentManager $documentManager,
    )
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->passwordEncoder = $passwordEncoder;
        $this->documentManager = $documentManager;
    }

    public function resetPassword(string $token, string $password, User $user): User
    {
        $expdat = $user->getResetTokenExpiry();
        if ($expdat < new \DateTime()) {
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
        } else {
            $user->setPassword($this->passwordEncoder->encodePassword($password));
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
        }
        return $user;
    }
}