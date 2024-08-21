<?php
declare(strict_types=1);
namespace App\EventListener;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\UserRepository;

class JWTRefreshListener implements EventSubscriberInterface
{
    private UserRepository $userRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        private readonly DocumentManager $documentManager,
        TokenStorageInterface $tokenStorage)//, Security $security)
    {
        $this->userRepository = $this->documentManager->getRepository(User::class);
        //$this->documentManager = $documentManager;
        $this->tokenStorage = $tokenStorage;

    }

    public static function getSubscribedEvents()
    {
        return [
            RefreshEvent::class => 'onJWTRefresh',
        ];
    }

    public function onJWTRefresh(RefreshEvent $event): void
    {
        $username = $this->tokenStorage->getToken()->getUserIdentifier();
        $user = $this->userRepository->findOneByUsername($username);
        if ($user) {
            $user->setLastLogin(new \DateTime());
            $this->documentManager->flush();
        }
    }
}