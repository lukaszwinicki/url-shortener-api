<?php

namespace App\ApiResource\Url\Get\List;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Url\Get\UrlOutput;
use App\Entity\Url;
use App\Security\SessionUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ListProvider implements ProviderInterface
{
    private const ITEMS_PER_PAGE = 30;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private Pagination $pagination
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();

        if (!$user instanceof SessionUser) {
            throw new UnauthorizedHttpException('Bearer', 'User is not authenticated');
        }

        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context) ?? self::ITEMS_PER_PAGE;

        $qb = $this->entityManager
            ->getRepository(Url::class)
            ->createQueryBuilder('u')
            ->where('u.session = :session')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('session', $user->getSession())
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

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
