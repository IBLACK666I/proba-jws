<?php
namespace App\Service;
use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class VerifyEmailService
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
    public function sendVeifyEmail(string $email, string $verifyLink): User
    {
        $user = $this->userRepository->findOneByEmail($email);
        $email_send = (new Email())
            ->from('no-reply@example.com')
            ->to($email)
            ->subject('Welcome')
            ->html("<p>Welcome</p><p><a href=\"$verifyLink\">Click link to activate account</a></p>");
        $this->mailer->send($email_send);
        return $user;
    }
}
