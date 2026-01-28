<?php

namespace App\ApiResource\Session\Get;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Session\Get\SessionOutput;
use App\Security\SessionUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class GetProvider implements ProviderInterface
{
    public function __construct(
        private Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SessionOutput
    {
        $user = $this->security->getUser();

        if (!$user instanceof SessionUser) {
            throw new UnauthorizedHttpException('Bearer', 'User is not authenticated');
        }

        $session = $user->getSession();

        return new SessionOutput(
            createdAt: $session->getCreatedAt()->format('c'),
            expiresAt: $session->getExpiresAt()->format('c'),
            jwtToken: null
        );
    }
}
