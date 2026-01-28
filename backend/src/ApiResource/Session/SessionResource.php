<?php

namespace App\ApiResource\Session;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Session\Create\CreateProcessor;
use App\ApiResource\Session\Get\GetProvider;
use App\ApiResource\Session\Get\SessionOutput;

#[ApiResource(
    shortName: 'Session',
    operations: [
        new Post(
            uriTemplate: '/session',
            security: 'is_granted("PUBLIC_ACCESS")',
            processor: CreateProcessor::class,
            input: false,
            output: SessionOutput::class,
            description: 'Create a new anonymous session and get JWT token'
        ),
        new Get(
            uriTemplate: '/session',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: GetProvider::class,
            output: SessionOutput::class,
            description: 'Get current session information'
        ),
    ]
)]
class SessionResource
{
}
