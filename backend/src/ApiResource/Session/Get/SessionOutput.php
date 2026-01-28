<?php

namespace App\ApiResource\Session\Get;

use ApiPlatform\Metadata\ApiProperty;

class SessionOutput
{
    public function __construct(
        #[ApiProperty(description: 'Session creation date in ISO 8601 format', example: '2024-01-15T10:30:00+00:00')]
        public string $createdAt,

        #[ApiProperty(description: 'Session expiration date in ISO 8601 format', example: '2024-12-31T23:59:59+00:00')]
        public string $expiresAt,

        #[ApiProperty(description: 'JWT token for authentication. Only returned when creating a new session (POST), null for GET requests', example: 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...')]
        public ?string $jwtToken
    ) {
    }
}
