<?php

namespace App\ApiResource\PublicUrl\Get\List;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Url\Get\UrlOutput;
use App\Entity\Url;
use App\Enum\Visibility;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class ListProvider implements ProviderInterface
{
    private const ITEMS_PER_PAGE = 30;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Pagination $pagination
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context) ?? self::ITEMS_PER_PAGE;

        $qb = $this->entityManager
            ->getRepository(Url::class)
            ->createQueryBuilder('u')
            ->where('u.visibility = :visibility')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('visibility', Visibility::Public)
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $qb->andWhere('u.expiresAt IS NULL OR u.expiresAt > :now')
            ->setParameter('now', new \DateTimeImmutable());

        $doctrinePaginator = new DoctrinePaginator($qb->getQuery());

        $urls = iterator_to_array($doctrinePaginator->getIterator());

        return array_map(fn(Url $url) => $this->mapToOutput($url), $urls);
    }

    private function mapToOutput(Url $url): UrlOutput
    {
        $output = new UrlOutput();
        $output->id = $url->getId();
        $output->shortCode = $url->getShortCode();
        $output->originalUrl = $url->getOriginalUrl();
        $output->visibility = $url->getVisibility();
        $output->expiresAt = $url->getExpiresAt()?->format('c');
        $output->createdAt = $url->getCreatedAt()->format('c');
        $output->clickCount = $url->getClickCount();

        return $output;
    }
}
