<?php declare(strict_types=1);

namespace App\EventListener;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;

readonly class JWTDecodedListener
{
    public function __construct(
        private DocumentManager $documentManager,
    )
    {
    }

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $username = $event->getPayload()['username'] ?? '';
        $user = $this->documentManager->getRepository(User::class)->findOneByUsername($username);

        if ($user) {
            $user->setLastActive(new \DateTime());
            $this->documentManager->flush();
        }
    }
}