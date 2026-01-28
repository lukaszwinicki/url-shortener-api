<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class UrlDeletionApiTest extends ApiTestCase
{
    public function testDeleteUrlWithoutAuthentication(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('DELETE', "/api/urls/{$url['id']}");

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteOwnUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteAnotherSessionUrl(): void
    {
        $session1 = $this->createSession();
        $session2 = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session1['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session2['jwtToken']);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteNonExistentUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest('DELETE', '/api/urls/99999', $session['jwtToken']);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeletedUrlNotInListing(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->makeAuthenticatedRequest('GET', '/api/urls', $session['jwtToken']);
        $data = $this->assertJsonResponse();

        $this->assertEquals(0, count($data));
        $this->assertEmpty($data);
    }

    public function testDeletedUrlNotAccessibleForStats(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->makeAuthenticatedRequest('GET', "/api/urls/{$url['id']}/stats", $session['jwtToken']);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDeleteUrlTwice(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
