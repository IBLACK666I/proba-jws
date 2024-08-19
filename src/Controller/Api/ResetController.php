<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class ResetController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/users/request-reset-password')]
    public function requestResetPassword(Request $request, MailerInterface $mailer): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;

        // TODO create UserRepository class with method findOneByUsername(string $username), and change all the places where you want to find the user by username/email address.
        $user = $this->userRepository->findOneByUsername($username);

        if (!$user) {
            return new JsonResponse(['message' => 'Check if email is correct'], Response::HTTP_BAD_REQUEST);
        }

        $resetToken = Uuid::v4()->toRfc4122();
        $user->setResetToken($resetToken);
        $user->setResetTokenExpiry((new \DateTime())->modify('+1 hour'));
        $this->documentManager->flush();

        $resetLink = $this->generateUrl('app_reset_resetpassword', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->html("<p>To reset your password, please click the link below:</p><p><a href=\"$resetLink\">Reset Password</a></p>");

        $mailer->send($email);

        return new JsonResponse(['email' => $user->getEmail(), 'message' => 'Password reset email sent'], Response::HTTP_OK);
    }
}