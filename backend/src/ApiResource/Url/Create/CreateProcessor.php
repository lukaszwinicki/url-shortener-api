<?php

namespace App\ApiResource\Url\Create;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Url\Get\UrlOutput;
use App\Entity\Url;
use App\Security\SessionUser;
use App\Service\ShortUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ShortUrlGenerator $shortener,
        private Security $security,
        private LoggerInterface $logger
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UrlOutput
    {
        $this->logger->info('CreateProcessor - Input data type: ' . get_debug_type($data));

        if (!$data instanceof CreateInput) {
            throw new \RuntimeException('Expected CreateInput, got ' . get_debug_type($data));
        }

        $this->logger->info('CreateProcessor - Original URL from input: ' . ($data->originalUrl ?? 'NULL'));

        $user = $this->security->getUser();

        if (!$user instanceof SessionUser) {
            throw new UnauthorizedHttpException('Bearer', 'User is not authenticated');
        }

        $url = new Url();
        $url->setOriginalUrl($data->originalUrl);
        $url->setVisibility($data->visibility);
        $url->setSession($user->getSession());

        if ($data->customAlias) {
            $shortCode = $this->shortener->validateCustomAlias($data->customAlias);
        } else {
            $shortCode = $this->shortener->generateShortCode();
        }

        $url->setShortCode($shortCode);

        if ($data->expiration) {
            $expiresAt = $this->parseExpiration($data->expiration);
            $url->setExpiresAt($expiresAt);
        }

        $this->entityManager->persist($url);
        $this->entityManager->flush();

        return $this->mapToOutput($url);
    }

    private function parseExpiration(?string $expiration): ?\DateTimeImmutable
    {
        return match ($expiration) {
            '1h' => new \DateTimeImmutable('+1 hour'),
            '1d' => new \DateTimeImmutable('+1 day'),
            '1w' => new \DateTimeImmutable('+1 week'),
            default => null
        };
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
