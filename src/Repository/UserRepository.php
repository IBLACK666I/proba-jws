<?php

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class UserRepository extends DocumentManager
{
    public function findOneByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
    public function findOneByToken(string $token): ?User
    {
        return $this->findOneBy(['resetTokenExpiry' => $token]);
    }
}