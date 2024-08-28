<?php

namespace App\Service;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use App\Security\Encoder\PasswordEncoder;

class EmailAndDataService
{
    private UserRepository $userRepository;
    private DocumentManager $documentManager;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private PasswordEncoder $passwordEncoder;


    public function __construct(
        DocumentManager       $documentManager,
        PasswordEncoder       $passwordEncoder,
        private UserValidator $userValidator,
        MailerInterface       $mailer,
        UrlGeneratorInterface $urlGenerator)
    {
        $this->userRepository = $documentManager->getRepository(User::class);
        $this->documentManager = $documentManager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->passwordEncoder = $passwordEncoder;
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

    public function ActivateUser(string $token): JsonResponse
    {
        $user = $this->userRepository->findOneByTokenVerify($token);
        if ($user === null) {
            return new JsonResponse(['message' => 'Check link'], Response::HTTP_NOT_FOUND);
        } else {
            $user->setActive(true);
            $user->setVerifyToken(null);
            $user->setLastActive(new \DateTime());
            $this->documentManager->flush();
            return new JsonResponse(['message' => 'Account is now created and verified '], Response::HTTP_OK);
        }
    }

    public function resetPassword(string $token, string $password, string $password_confirmation): JsonResponse
    {
        $user = $this->userRepository->findOneByToken($token);
        if (!$this->userValidator->validatePassword($password)) {
            return new JsonResponse(['message' => 'Password must be at least 10 letter long contain upper and lower case letter number and special character'], Response::HTTP_BAD_REQUEST);
        }
        if (!$user) {
            return new JsonResponse(['message' => 'check the link'], Response::HTTP_NOT_FOUND);
        }
        if ($password != $password_confirmation) {
            return new JsonResponse(['message' => 'Passwords must match.'], Response::HTTP_BAD_REQUEST);
        }
        $expdat = $user->getResetTokenExpiry();
        if ($expdat < new \DateTime()) {
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
        } else {
            $user->setPassword($this->passwordEncoder->encodePassword($password));
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $this->documentManager->flush();
        }
        return new JsonResponse(['message' => 'Password reset successfully.'], Response::HTTP_OK);
    }

    public function registerUser(string $username, string $email, string $password, string $verifyToken): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($this->passwordEncoder->encodePassword($password));
        $user->setDateCreated(new \DateTime());
        $user->setVerifyToken($verifyToken);
        $user->setActive(false);
        $this->documentManager->persist($user);
        $this->documentManager->flush();
        return $user;
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
        $user = $this->registerUser($username, $email, $password, $verifyToken);
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