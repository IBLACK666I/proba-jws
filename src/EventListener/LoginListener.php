<?php declare(strict_types=1);

namespace App\EventListener;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

readonly class LoginListener
{
    public function __construct(
        private DocumentManager $documentManager,
    )
    {
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $username = $token->getUser()->getUsername();
        $user = $this->documentManager->getRepository(User::class)->findOneByUsername($username);

        if ($user) {
            $user->setLastLogin(new \DateTime());
            $user->setLastActive(new \DateTime());
            $this->documentManager->flush();
        }
    }
}