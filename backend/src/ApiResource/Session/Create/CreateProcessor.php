<?php

namespace App\ApiResource\Session\Create;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Session\Get\SessionOutput;
use App\Entity\Session;
use App\Security\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\Uuid;

class CreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SessionOutput
    {
        $session = new Session();
        $session->setSessionToken(Uuid::uuid4()->toString());
        $session->setCreatedAt(new \DateTimeImmutable());
        $session->setExpiresAt(new \DateTimeImmutable('+30 days'));

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        $sessionUser = new SessionUser($session);
        $token = $this->jwtManager->create($sessionUser);

        return new SessionOutput(
            createdAt: $session->getCreatedAt()->format('c'),
            expiresAt: $session->getExpiresAt()->format('c'),
            jwtToken: $token
        );
    }
}
