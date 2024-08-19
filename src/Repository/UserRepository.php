<?php

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function findOneByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => strtolower($username)]);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneByToken(string $token): ?User
    {
        return $this->findOneBy(['resetToken' => $token]);
    }
}