<?php declare(strict_types=1);

namespace App\Security\Encoder;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PasswordEncoder implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        $hashMethod = getenv('HASH_METHOD');
        $hashKey = getenv('HASH_KEY');

        return hash_hmac($hashMethod, $plainPassword, $hashKey);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $hashedPassword === $this->hash($plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }

    public function encodePassword(string $plainPassword): string
    {
        return $this->hash($plainPassword);
    }
}
