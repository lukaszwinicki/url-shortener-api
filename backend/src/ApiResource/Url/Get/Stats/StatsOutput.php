<?php

namespace App\ApiResource\Url\Get\Stats;

use ApiPlatform\Metadata\ApiProperty;

class StatsOutput
{
    public function __construct(
        #[ApiProperty(description: 'URL identifier', example: 123)]
        public int $urlId,

        #[ApiProperty(description: 'Short code of the URL', example: 'abc123')]
        public string $shortCode,

        #[ApiProperty(description: 'Original long URL', example: 'https://example.com/page')]
        public string $originalUrl,

        #[ApiProperty(description: 'Total number of clicks/redirects', example: 150)]
        public int $totalClicks,

        #[ApiProperty(description: 'URL creation date in ISO 8601 format', example: '2024-01-15T10:30:00+00:00')]
        public string $createdAt
    ) {
    }
}
