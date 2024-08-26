<?php

namespace App\Service;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ResetEmailService
{
    private UserRepository $userRepository;
    private DocumentManager $documentManager;
    private MailerInterface $mailer;

    public function __construct(DocumentManager $documentManager, MailerInterface $mailer)
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->documentManager = $documentManager;
        $this->mailer = $mailer;
    }

    public function sendPassResetEmail(string $email, string $resetLink): User
    {
        $user = $this->userRepository->findOneByEmail($email);
        $email_send = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->html("<p>To reset your password, please click the link below:</p><p><a href=\"$resetLink\">Reset Password</a></p>");
        $this->mailer->send($email_send);
        return $user;
    }
}