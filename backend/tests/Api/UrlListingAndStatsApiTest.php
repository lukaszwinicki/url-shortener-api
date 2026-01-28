<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class UrlListingAndStatsApiTest extends ApiTestCase
{
    public function testGetUrlsWithoutAuthentication(): void
    {
        $this->request('GET', '/api/urls');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetUrlsWithAuthentication(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest('GET', '/api/urls', $session['jwtToken']);

        $data = $this->assertJsonResponse();

        $this->assertIsArray($data);
    }

    public function testGetUrlsReturnsOnlySessionUrls(): void
    {
        $session1 = $this->createSession();
        $session2 = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session1['jwtToken'],
            ['originalUrl' => 'https://example1.com']
        );

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session2['jwtToken'],
            ['originalUrl' => 'https://example2.com']
        );

        $this->makeAuthenticatedRequest('GET', '/api/urls', $session1['jwtToken']);
        $data1 = $this->assertJsonResponse();

        $this->makeAuthenticatedRequest('GET', '/api/urls', $session2['jwtToken']);
        $data2 = $this->assertJsonResponse();

        $this->assertEquals(1, count($data1));
        $this->assertEquals(1, count($data2));
        $this->assertEquals('https://example1.com', $data1[0]['originalUrl']);
        $this->assertEquals('https://example2.com', $data2[0]['originalUrl']);
    }

    public function testGetUrlsPagination(): void
    {
        $session = $this->createSession();

        for ($i = 1; $i <= 25; $i++) {
            $this->makeAuthenticatedRequest(
                'POST',
                '/api/urls',
                $session['jwtToken'],
                ['originalUrl' => "https://example{$i}.com"]
            );
        }

        $this->makeAuthenticatedRequest('GET', '/api/urls?page=1', $session['jwtToken']);
        $data = $this->assertJsonResponse();

        $this->assertIsArray($data);
        $this->assertEquals(25, count($data));
    }

    public function testGetUrlStatsWithoutAuthentication(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('GET', "/api/urls/{$url['id']}/stats");

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetUrlStatsForOwnUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('GET', "/api/urls/{$url['id']}/stats", $session['jwtToken']);

        $data = $this->assertJsonResponse();

        $this->assertArrayHasKey('totalClicks', $data);
        $this->assertEquals(0, $data['totalClicks']);
    }

    public function testGetUrlStatsForAnotherSessionUrl(): void
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

        $this->makeAuthenticatedRequest('GET', "/api/urls/{$url['id']}/stats", $session2['jwtToken']);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetUrlStatsForNonExistentUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest('GET', '/api/urls/99999/stats', $session['jwtToken']);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
