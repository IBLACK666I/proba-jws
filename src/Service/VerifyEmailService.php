<?php

namespace App\Service;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class VerifyEmailService
{
    private UserRepository $userRepository;
    private DocumentManager $documentManager;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(DocumentManager             $documentManager,
                                MailerInterface             $mailer,
                                UrlGeneratorInterface       $urlGenerator,
                                private UserValidator       $userValidator,
                                private UserRegisterService $userRegisterService,
    )
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->documentManager = $documentManager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;

    }

    public function sendVeifyEmail(string $email, string $password): JsonResponse
    {
        $username = strtolower($email);
        //$user = $this->userRepository->findOneByEmail($email);
        if (!$this->userValidator->isUsernameAvailable($username)) {
            return new JsonResponse(['message' => 'Email is not available'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->userValidator->validateEmail($email)) {
            return new JsonResponse(['message' => 'Email is not valid'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->userValidator->validatePassword($password)) {
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }
        $verifyToken = Uuid::v4()->toRfc4122();
        $user = $this->userRegisterService->registerUser($username, $email, $password, $verifyToken);
        $verifyLink = $this->urlGenerator->generate('app_verify', ['token' => $verifyToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email_send = (new Email())
            ->from('no-reply@example.com')
            ->to($email)
            ->subject('Welcome')
            ->html("<p>Welcome</p><p><a href=\"$verifyLink\">Click link to activate account</a></p>");
        $this->mailer->send($email_send);
        return new JsonResponse(['message' => 'Verification email was sent'], Response::HTTP_CREATED);
    }
}
