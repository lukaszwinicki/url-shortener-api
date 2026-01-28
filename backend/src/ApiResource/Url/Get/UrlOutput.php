<?php

namespace App\ApiResource\Url\Get;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Visibility;

class UrlOutput
{
    #[ApiProperty(description: 'Unique identifier of the URL', example: 123)]
    public ?int $id = null;

    #[ApiProperty(description: 'Generated short code for the URL', example: 'abc123')]
    public ?string $shortCode = null;

    #[ApiProperty(description: 'Original long URL', example: 'https://example.com/very/long/url')]
    public ?string $originalUrl = null;

    #[ApiProperty(
        description: 'Visibility status of the URL',
        example: 'public'
    )]
    public Visibility $visibility = Visibility::Public;

    #[ApiProperty(description: 'Expiration date in ISO 8601 format. Null if no expiration', example: '2024-12-31T23:59:59+00:00')]
    public ?string $expiresAt = null;

    #[ApiProperty(description: 'Creation date in ISO 8601 format', example: '2024-01-15T10:30:00+00:00')]
    public ?string $createdAt = null;

    #[ApiProperty(description: 'Total number of clicks/redirects', example: 42)]
    public int $clickCount = 0;
}
