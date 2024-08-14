<?php
namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserValidator
{
    public function __construct(private readonly DocumentManager $documentManager) {}

    public function validate(string $username, string $email, string $password): ?JsonResponse
    {
        $email_lower = strtolower(trim($email));

        if (!filter_var($email_lower, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'message' => 'Invalid email address',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password)) {
            return new JsonResponse([
                'message' => 'Password must be at least 10 characters long and contain at least one lowercase letter, one uppercase letter, one digit, and one special character',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->documentManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (null !== $user) {
            return new JsonResponse([
                'message' => 'This username is already taken.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }
}
