<?php

namespace App\Security;

use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SessionUserProvider implements UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $session = $this->entityManager
            ->getRepository(Session::class)
            ->findOneBy(['sessionToken' => $identifier]);

        if (!$session) {
            throw new UserNotFoundException(sprintf('Session with token "%s" not found.', $identifier));
        }

        if ($session->getExpiresAt() < new \DateTimeImmutable()) {
            throw new UserNotFoundException('Session has expired.');
        }

        return new SessionUser($session);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SessionUser) {
            throw new \InvalidArgumentException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SessionUser::class === $class || is_subclass_of($class, SessionUser::class);
    }
}
