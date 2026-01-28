<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class PublicUrlsApiTest extends ApiTestCase
{
    public function testGetPublicUrlsWithoutAuthentication(): void
    {
        $this->request('GET', '/api/public');

        $data = $this->assertJsonResponse();

        $this->assertIsArray($data);
    }

    public function testGetPublicUrlsReturnsOnlyPublicUrls(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://public.com',
                'visibility' => 'public'
            ]
        );

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://private.com',
                'visibility' => 'private'
            ]
        );

        $this->request('GET', '/api/public');
        $data = $this->assertJsonResponse();

        foreach ($data as $url) {
            $this->assertEquals('public', $url['visibility']);
        }
    }

    public function testGetPublicUrlsDoesNotIncludeDeletedUrls(): void
    {
        $session = $this->createSession();

        $alias = 'tst' . substr(md5(uniqid()), 0, 4);
        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => $alias
            ]
        );

        $url = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->request('GET', '/api/public');
        $dataBefore = $this->assertJsonResponse();
        $countBefore = count($dataBefore);

        $this->makeAuthenticatedRequest('DELETE', "/api/urls/{$url['id']}", $session['jwtToken']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->request('GET', '/api/public');
        $dataAfter = $this->assertJsonResponse();
        $countAfter = count($dataAfter);

        $this->assertEquals($countBefore - 1, $countAfter);

        foreach ($dataAfter as $publicUrl) {
            $this->assertNotEquals($alias, $publicUrl['shortCode']);
        }
    }

    public function testGetPublicUrlsPagination(): void
    {
        $session = $this->createSession();

        for ($i = 1; $i <= 25; $i++) {
            $this->makeAuthenticatedRequest(
                'POST',
                '/api/urls',
                $session['jwtToken'],
                [
                    'originalUrl' => "https://example{$i}.com",
                    'visibility' => 'public'
                ]
            );
        }

        $this->request('GET', '/api/public?page=1');
        $data = $this->assertJsonResponse();

        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(25, count($data));
    }

    public function testGetPublicUrlsDoesNotExposeSessionInfo(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $this->request('GET', '/api/public');
        $data = $this->assertJsonResponse();

        foreach ($data as $url) {
            $this->assertArrayNotHasKey('session', $url);
            $this->assertArrayNotHasKey('sessionId', $url);
        }
    }
}
