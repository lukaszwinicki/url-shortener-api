<?php

namespace App\ApiResource\Url\Get\Stats;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\UrlLog;
use App\Entity\Url;
use App\Security\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class StatsProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): StatsOutput
    {
        $user = $this->security->getUser();

        if (!$user instanceof SessionUser) {
            throw new UnauthorizedHttpException('Bearer', 'User is not authenticated');
        }

        $url = $this->entityManager->getRepository(Url::class)->find($uriVariables['id']);

        if (!$url) {
            throw new NotFoundHttpException('URL not found');
        }

        if ($url->getSession() !== $user->getSession()) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return new StatsOutput(
            urlId: $url->getId(),
            shortCode: $url->getShortCode(),
            originalUrl: $url->getOriginalUrl(),
            totalClicks: $url->getClickCount(),
            createdAt: $url->getCreatedAt()->format('c')
        );
    }
}
