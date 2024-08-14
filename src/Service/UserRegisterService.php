<?php
namespace App\Service;

use App\Document\User;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;

class UserRegisterService
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly PasswordEncoder $passwordEncoder
    ) {}

    public function register(string $username, string $email, string $password): User
    {
        $user = new User();
        $user->setUsername(strtolower($username));
        $user->setEmail(strtolower(trim($email)));
        $user->setPassword($this->passwordEncoder->encodePassword($password));
        $user->setDateCreated(new \DateTime());
        $user->setActive(true);
        $user->setLastLogin(new \DateTime());
        $user->setLastActive(new \DateTime());

        $this->documentManager->persist($user);
        $this->documentManager->flush();

        return $user;
    }
}
