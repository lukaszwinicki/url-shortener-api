<?php

namespace App\ApiResource\Url;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Url\Create\CreateInput;
use App\ApiResource\Url\Create\CreateProcessor;
use App\ApiResource\Url\Delete\DeleteProcessor;
use App\ApiResource\Url\Delete\DeleteProvider;
use App\ApiResource\Url\Get\List\ListProvider;
use App\ApiResource\Url\Get\Stats\StatsOutput;
use App\ApiResource\Url\Get\Stats\StatsProvider;
use App\ApiResource\Url\Get\UrlOutput;
use App\Entity\Url;

#[ApiResource(
    class: Url::class,
    shortName: 'Url',
    operations: [
        new GetCollection(
            uriTemplate: '/urls',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: ListProvider::class,
            output: UrlOutput::class,
            description: 'Get a paginated list of URLs created in the current session'
        ),
        new Post(
            uriTemplate: '/urls',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            input: CreateInput::class,
            output: UrlOutput::class,
            processor: CreateProcessor::class,
            name: 'url_create',
            description: 'Create a new shortened URL'
        ),
        new Get(
            uriTemplate: '/urls/{id}/stats',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: StatsProvider::class,
            output: StatsOutput::class,
            name: 'url_stats',
            description: 'Get click statistics for a specific URL'
        ),
        new Delete(
            uriTemplate: '/urls/{id}',
            security: 'is_granted("IS_AUTHENTICATED_FULLY") and object.getSession() == user.getSession()',
            provider: DeleteProvider::class,
            processor: DeleteProcessor::class,
            description: 'Soft delete a URL (marks as deleted, preserves data)'
        ),
    ]
)]
class UrlResource
{
}
