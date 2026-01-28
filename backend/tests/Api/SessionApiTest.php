<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class SessionApiTest extends ApiTestCase
{
    public function testCreateSession(): void
    {
        $this->request('POST', '/api/session');

        $data = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->assertArrayHasKey('jwtToken', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('expiresAt', $data);
        $this->assertNotNull($data['jwtToken']);
        $this->assertNotEmpty($data['jwtToken']);
    }

    public function testGetSessionWithoutAuthentication(): void
    {
        $this->request('GET', '/api/session');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetSessionWithAuthentication(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest('GET', '/api/session', $session['jwtToken']);

        $data = $this->assertJsonResponse();

        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('expiresAt', $data);
    }

    public function testGetSessionWithInvalidToken(): void
    {
        $this->makeAuthenticatedRequest('GET', '/api/session', 'invalid-token');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testMultipleSessionsHaveDifferentTokens(): void
    {
        $session1 = $this->createSession();
        $session2 = $this->createSession();

        $this->assertNotEquals($session1['jwtToken'], $session2['jwtToken']);
    }
}
