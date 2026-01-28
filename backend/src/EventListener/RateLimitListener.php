<?php

namespace App\EventListener;

use App\Security\SessionUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: 'kernel.request', priority: 10)]
class RateLimitListener
{
    public function __construct(
        #[Autowire(service: 'limiter.url_creation')] private RateLimiterFactory $urlCreationLimiter,
        private Security $security,
        #[Autowire(param: 'rate_limiter.url_creation.limit')] private int $limit,
        #[Autowire(param: 'rate_limiter.url_creation.interval_seconds')] private int $intervalSeconds
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        if ($request->attributes->get('_route') !== 'url_create') {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof SessionUser) {
            return;
        }

        $session = $user->getSession();
        if (!$session) {
            return;
        }

        $sessionId = $session->getId();
        if (!$sessionId) {
            return;
        }

        $limiter = $this->urlCreationLimiter->create($sessionId);

        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();

            throw new TooManyRequestsHttpException(
                $retryAfter,
                sprintf(
                    'Rate limit exceeded. Maximum %d URLs per %d seconds. Try again in %d seconds.',
                    $this->limit,
                    $this->intervalSeconds,
                    $retryAfter
                )
            );
        }
    }
}
