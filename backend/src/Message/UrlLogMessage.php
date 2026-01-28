<?php

namespace App\Message;

class UrlLogMessage implements AsyncMessageInterface
{
    public function __construct(
        public readonly int $urlId,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly ?string $referer,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
}
