<?php

namespace App\ApiResource\PublicUrl;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\PublicUrl\Get\List\ListProvider;
use App\ApiResource\Url\Get\UrlOutput;

#[ApiResource(
    shortName: 'PublicUrl',
    operations: [
        new GetCollection(
            uriTemplate: '/public',
            security: 'is_granted("PUBLIC_ACCESS")',
            provider: ListProvider::class,
            output: UrlOutput::class,
            description: 'Returns a list of all public, non-deleted, non-expired URLs'
        ),
    ]
)]
class PublicUrlResource
{
}
