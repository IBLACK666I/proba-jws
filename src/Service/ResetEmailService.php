<?php

namespace App\Service;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
//use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
class ResetEmailService
{
    private UserRepository $userRepository;
    private DocumentManager $documentManager;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        DocumentManager $documentManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator)
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->documentManager = $documentManager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;

    }

    public function sendPassResetEmail(string $username): JsonResponse
    {
        if ($username === null) {
            return new JsonResponse(['message' => 'Input your username first'], Response::HTTP_BAD_REQUEST);
        }
        $user = $this->userRepository->findOneByUsername($username);
        if (!$user) {
            return new JsonResponse(['message' => 'Check if email is correct'], Response::HTTP_BAD_REQUEST);
        }


        $resetToken = Uuid::v4()->toRfc4122();
        $user->setResetToken($resetToken);
        $user->setResetTokenExpiry((new \DateTime())->modify('+1 hour'));
        $this->documentManager->flush();
        $resetLink = $this->urlGenerator->generate('app_reset_resetpassword', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email_send = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->html("<p>To reset your password, please click the link below:</p><p><a href=\"$resetLink\">Reset Password</a></p>");
        $this->mailer->send($email_send);
        return new JsonResponse(['email' => $user->getEmail(), 'message' => 'Password reset email sent'], Response::HTTP_OK);
    }
}