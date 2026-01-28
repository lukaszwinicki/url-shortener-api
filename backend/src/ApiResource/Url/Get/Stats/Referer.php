<?php

namespace App\ApiResource\Url\Get\Stats;

use ApiPlatform\Metadata\ApiProperty;

class Referer
{
    public function __construct(
        #[ApiProperty(description: 'Referring URL or null for direct visits', example: 'https://google.com')]
        public ?string $referer,

        #[ApiProperty(description: 'Number of clicks from this referer', example: 42)]
        public int $count
    ) {
    }
}
