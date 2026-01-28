<?php

namespace App\Controller;

use App\Entity\Url;
use App\Message\UrlLogMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles HTTP redirects for short URLs outside of the REST API.
 * This is intentionally NOT part of API Platform - the API returns JSON,
 * while this controller performs browser redirects. Clean separation of concerns.
 */
class RedirectController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus
    ) {
    }

    #[Route('/{shortCode}', name: 'redirect', requirements: ['shortCode' => '[a-zA-Z0-9]{6,8}'], methods: ['GET'])]
    public function __invoke(string $shortCode, Request $request): RedirectResponse
    {
        $url = $this->entityManager->getRepository(Url::class)->findOneBy(['shortCode' => $shortCode]);

        if (!$url || $url->isDeleted()) {
            throw new NotFoundHttpException('Short URL not found');
        }

        if ($url->isExpired()) {
            throw new NotFoundHttpException('Short URL has expired');
        }

        $this->messageBus->dispatch(new UrlLogMessage(
            $url->getId(),
            $request->getClientIp(),
            $request->headers->get('User-Agent'),
            $request->headers->get('Referer'),
            new \DateTimeImmutable()
        ));

        return new RedirectResponse($url->getOriginalUrl(), 302);
    }
}
