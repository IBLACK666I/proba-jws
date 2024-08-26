<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\Repository\UserRepository;
use App\Service\ResetEmailService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class ResetController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly DocumentManager   $documentManager,
        private readonly ResetEmailService $resetEmailService,
    )
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
    }

    #[Route('/api/request-reset-password')]
    public function requestResetPassword(Request $request, MailerInterface $mailer): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
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
        $resetLink = $this->generateUrl('app_reset_resetpassword', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->resetEmailService->sendPassResetEmail($user->getEmail(), $resetLink);
        return new JsonResponse(['email' => $user->getEmail(), 'message' => 'Password reset email sent'], Response::HTTP_OK);
    }
}