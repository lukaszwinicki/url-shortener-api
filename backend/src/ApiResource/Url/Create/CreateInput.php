<?php

namespace App\ApiResource\Url\Create;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Visibility;
use Symfony\Component\Validator\Constraints as Assert;

class CreateInput
{
    #[Assert\NotBlank]
    #[Assert\Url]
    #[ApiProperty(
        description: 'The original URL to be shortened',
        example: 'https://example.com/very/long/url'
    )]
    public ?string $originalUrl = null;

    #[ApiProperty(
        description: 'Visibility of the shortened URL',
        example: 'public',
        default: 'public'
    )]
    public Visibility $visibility = Visibility::Public;

    #[Assert\Regex(pattern: '/^[a-zA-Z0-9]{6,8}$/', message: 'Custom alias must be 6-8 alphanumeric characters')]
    #[ApiProperty(
        description: 'Custom short code (6-8 alphanumeric characters). If not provided, one will be generated automatically',
        example: 'mylink',
        openapiContext: ['pattern' => '^[a-zA-Z0-9]{6,8}$']
    )]
    public ?string $customAlias = null;

    #[Assert\Choice(choices: ['1h', '1d', '1w'])]
    #[ApiProperty(
        description: 'Expiration period for the URL (1h = 1 hour, 1d = 1 day, 1w = 1 week). Null means no expiration',
        example: '1d',
        openapiContext: ['enum' => ['1h', '1d', '1w']]
    )]
    public ?string $expiration = null;
}
