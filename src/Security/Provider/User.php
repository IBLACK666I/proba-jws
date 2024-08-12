<?php declare(strict_types=1);

namespace App\Security\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Document\User as UserDocument;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class User implements UserProviderInterface
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        /** @var UserDocument|null $user */
        $userEntity = $this->documentManager->getRepository(UserDocument::class)->findOneBy([
            'username' => $identifier,
        ]);

        if (null === $userEntity) {
            throw new UserNotFoundException();
        }

        return $userEntity;
    }

    public function supportsClass($class): bool
    {
        return $class instanceof UserInterface || $class === UserDocument::class;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }
}
