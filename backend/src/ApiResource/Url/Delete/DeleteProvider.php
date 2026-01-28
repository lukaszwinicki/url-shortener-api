<?php

namespace App\ApiResource\Url\Delete;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;

class DeleteProvider implements ProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Url
    {
        $id = $uriVariables['id'] ?? null;

        if (!$id) {
            return null;
        }

        return $this->entityManager
            ->getRepository(Url::class)
            ->find($id);
    }
}
