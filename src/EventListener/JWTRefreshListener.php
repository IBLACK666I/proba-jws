<?php
declare(strict_types=1);
// src/EventListener/JWTRefreshListener.php
namespace App\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
class JWTRefreshListener implements EventSubscriberInterface
{
    private $documentManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(DocumentManager $documentManager, TokenStorageInterface $tokenStorage)//, Security $security)
    {
        $this->documentManager = $documentManager;
        $this->tokenStorage = $tokenStorage;

    }

    public static function getSubscribedEvents()
    {
        return [
            RefreshEvent::class => 'onJWTRefresh',
        ];
    }

    public function onJWTRefresh(RefreshEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user) {
            $user->setLastLogin(new \DateTime());
            $this->documentManager->persist($user);
            $this->documentManager->flush();
        }
    }
}
