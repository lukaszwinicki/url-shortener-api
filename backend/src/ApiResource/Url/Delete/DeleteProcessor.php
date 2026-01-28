<?php

namespace App\ApiResource\Url\Delete;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;

class DeleteProcessor implements ProcessorInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof Url) {
            throw new \InvalidArgumentException('Expected instance of Url');
        }

        $data->setDeletedAt(new \DateTimeImmutable());

        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }
}
