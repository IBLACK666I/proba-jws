<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetcahngeController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {}
    #[Route('/users/reset-password/{token}', name: 'app_reset_resetpassword')]
    public function resetPassword(Request $request, string $token, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $password = $requestData['password'] ?? null;
        $password_confirmation = $requestData['password'] ?? null;
        if($password != $password_confirmation) {
            return new JsonResponse(['message'=>'Passwords must match.'], Response::HTTP_BAD_REQUEST);
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password)) {
            return new JsonResponse([
                'message' => 'Password must be at least 10 characters long and contain at least one lowercase letter, one uppercase letter, one digit, and one special character',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->documentManager->getRepository(User::class)->findOneBy([
            'ResetToken' => $token
        ]);
        //return new JsonResponse(['token'=>$user->getResetToken()], Response::HTTP_OK);
        if($user === null) {
            return new JsonResponse(['message' => 'Invalid reset token'], Response::HTTP_NOT_FOUND);
        }
        else{
            $expdat = $user->getResetTokenExpiry();
            if ($expdat < new \DateTime()) {
                $user->setResetToken(null);
                $user->setResetTokenExpiry(null);
                $this->documentManager->flush();
                return new JsonResponse(['message' => 'Token timed out','time'=>new \DateTime()], Response::HTTP_BAD_REQUEST);
            }
            else
            {
                $user->setPassword($passwordEncoder->encodePassword($password));
                $user->setResetToken(null);
                $user->setResetTokenExpiry(null);
                $this->documentManager->flush();
                return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);

            }
        }
    }
}
