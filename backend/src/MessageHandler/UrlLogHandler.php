<?php

namespace App\MessageHandler;

use App\Entity\UrlLog;
use App\Entity\Url;
use App\Message\UrlLogMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UrlLogHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(UrlLogMessage $message): void
    {
        /** @var ?Url $url */
        $url = $this->entityManager
            ->getRepository(Url::class)
            ->find($message->urlId);

        if (!$url) {
            $this->logger->warning('URL not found for URL log', ['urlId' => $message->urlId]);
            return;
        }

        $urlLog = new UrlLog();
        $urlLog->setUrl($url);
        $urlLog->setCreatedAt($message->createdAt);
        $urlLog->setIpAddress($message->ipAddress);
        $urlLog->setUserAgent($message->userAgent);
        $urlLog->setReferer($message->referer);

        $this->entityManager->persist($urlLog);

        $url->incrementClickCount();
        $this->entityManager->persist($url);

        $this->entityManager->flush();

        $this->logger->info('URL access logged', [
            'urlId' => $message->urlId,
            'shortCode' => $url->getShortCode(),
            'totalClicks' => $url->getClickCount(),
        ]);
    }
}
