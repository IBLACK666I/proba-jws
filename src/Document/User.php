<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[MongoDB\Document(collection: 'users', repositoryClass: UserRepository::class)]
class User extends Base implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[MongoDB\Field(name: 'username', type: 'string')]
    private ?string $username = null;

    #[MongoDB\Field(name: 'firstname', type: 'string')]
    private ?string $firstname = null;

    #[MongoDB\Field(name: 'password', type: 'string')]
    private string $password;
    #[MongoDB\Field(name: 'email', type: 'string')]
    private ?string $email = null;
    #[MongoDB\Field(name: 'date_created', type: 'date')]
    private ?\DateTimeInterface $dateCreated;

    #[MongoDB\Field(name: 'active', type: 'bool')]
    private bool $active = true;

    #[MongoDB\Field(name: 'last_login', type: 'date', nullable: true)]
    private ?\DateTimeInterface $lastLogin ;

    #[MongoDB\Field(name: 'last_active', type: 'date', nullable: true)]
    private ?\DateTimeInterface $lastActive = null;

    // TODO variable names should start with lowercase letters, same as database field name (should be also snake case)
    #[MongoDB\Field(name: 'reset_token', type: 'string')]
    private ?string $resetToken = null;
    #[MongoDB\Field(name: 'reset_token_expiry', type: 'date')]
    private ?\DateTimeInterface $resetTokenExpiry;

    #[MongoDB\Field(name: 'verify_token', type: 'string')]
    private ?string $verifyToken = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }
    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }
    public function getVerifyToken(): ?string
    {
        return $this->verifyToken;
    }
    public function setVerifyToken(?string $verifyToken): void
    {
        $this->verifyToken = $verifyToken;
    }


    public function __construct()
    {
        //$this->dateCreated = new \DateTime();
        //$this->lastLogin = new \DateTime();
        //$this->lastActive = new \DateTime();
        //$this->resetTokenExpiry = new \DateTime();
    }
    public function getDateCreated(): \DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }
    public function getResetTokenExpiry(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiry;
    }

    public function setResetTokenExpiry(?\DateTimeInterface $resetTokenExpiry): void
    {
        $this->resetTokenExpiry = $resetTokenExpiry;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLastLogin(): \DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getLastActive(): \DateTimeInterface
    {
        return $this->lastActive;
    }

    public function setLastActive(\DateTimeInterface $lastActive): void
    {
        $this->lastActive = $lastActive;
    }
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {

    }

    /**
     * Returns the identifier for this user (e.g. username or email address).
     */
    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }
}