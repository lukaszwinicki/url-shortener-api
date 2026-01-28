<?php

namespace App\Security;

use App\Entity\Session;
use Symfony\Component\Security\Core\User\UserInterface;

class SessionUser implements UserInterface
{
    public function __construct(private Session $session)
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->session->getSessionToken();
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // No credentials to erase for sessionless authentication
    }

    public function getSession(): Session
    {
        return $this->session;
    }
}
