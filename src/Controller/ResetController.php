<?php
declare(strict_types=1);

namespace App\Controller;

use App\Document\User;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

// TODO move this controller to Api namespace
class ResetController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        // TODO mailer can be passed on mehtod level, it is used only at requestResetPassword method
        private readonly MailerInterface $mailer
    ) {}

    #[Route('/users/request-reset-password')]
    public function requestResetPassword(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $email = $requestData['email'] ?? null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }

        // TODO find user by username (it should be user's unique identifier ref. \App\Document\User::getUserIdentifier)
        // TODO create UserRepository class with method findOneByUsername(string $username), and change all the places where you want to find the user by username/email address.
        $user = $this->documentManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            // TODO change the mesage, don't tell that the user doesn't exist in the database.
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $resetToken = Uuid::v4()->toRfc4122();
        $user->setResetToken($resetToken);
        $user->setResetTokenExpiry((new \DateTime())->modify('+1 hour'));
        $this->documentManager->flush();

        $resetLink = $this->generateUrl('reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->html("<p>To reset your password, please click the link below:</p><p><a href=\"$resetLink\">Reset Password</a></p>");

        $this->mailer->send($email);

        return new JsonResponse(['email'=>$user->getEmail(),'message' => 'Password reset email sent'], Response::HTTP_OK);
    }

    #[Route('/users/reset-password/{token}')]
    public function resetPassword(Request $request, string $token, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // TODO There should also be a password_confirmation that should be the same as the password, unless that part is handled by other front-end application
        $password = $requestData['password'] ?? null;
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
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
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
