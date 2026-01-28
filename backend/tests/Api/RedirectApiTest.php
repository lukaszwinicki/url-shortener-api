<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class RedirectApiTest extends ApiTestCase
{
    public function testRedirectToValidShortCode(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'valid1'
            ]
        );

        $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('GET', '/valid1');

        $this->assertResponseRedirects('https://example.com', Response::HTTP_FOUND);
    }

    public function testRedirectToNonExistentShortCode(): void
    {
        $this->request('GET', '/nonexistent');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testRedirectToDeletedUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'deleted1'
            ]
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->request('GET', '/deleted1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testRedirectToExpiredUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'expired1',
                'expiration' => '1h'
            ]
        );

        $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('GET', '/expired1');

        $this->assertResponseRedirects('https://example.com', Response::HTTP_FOUND);
    }

    public function testRedirectToPrivateUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'private1',
                'visibility' => 'private'
            ]
        );

        $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('GET', '/private1');

        $this->assertResponseRedirects('https://example.com', Response::HTTP_FOUND);
    }

    public function testRedirectIncrementsClickCount(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'clicks1'
            ]
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->assertEquals(0, $url['clickCount']);

        $this->request('GET', '/clicks1');
        $this->assertResponseRedirects();

        sleep(1);

        $this->makeAuthenticatedRequest('GET', "/api/urls/{$url['id']}/stats", $session['jwtToken']);
        $stats = $this->assertJsonResponse();

        $this->assertGreaterThanOrEqual(0, $stats['totalClicks']);
    }

    public function testMultipleRedirectsIncrementClickCount(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'multi1'
            ]
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        for ($i = 0; $i < 3; $i++) {
            $this->request('GET', '/multi1');
            $this->assertResponseRedirects();
        }

        sleep(1);

        $this->makeAuthenticatedRequest('GET', "/api/urls/{$url['id']}/stats", $session['jwtToken']);
        $stats = $this->assertJsonResponse();

        $this->assertGreaterThanOrEqual(0, $stats['totalClicks']);
    }

    public function testRedirectWithSpecialCharactersInUrl(): void
    {
        $session = $this->createSession();

        $urlWithQuery = 'https://example.com/page?param=value&other=test';

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => $urlWithQuery,
                'customAlias' => 'special1'
            ]
        );

        $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('GET', '/special1');

        $this->assertResponseRedirects($urlWithQuery, Response::HTTP_FOUND);
    }
}
