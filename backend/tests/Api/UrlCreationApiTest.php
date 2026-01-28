<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class UrlCreationApiTest extends ApiTestCase
{
    public function testCreateUrlWithoutAuthentication(): void
    {
        $this->request(
            'POST',
            '/api/urls',
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['originalUrl' => 'https://example.com'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateUrlWithMinimalData(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'https://example.com']
        );

        $data = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('shortCode', $data);
        $this->assertArrayHasKey('originalUrl', $data);
        $this->assertArrayHasKey('visibility', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('clickCount', $data);

        $this->assertEquals('https://example.com', $data['originalUrl']);
        $this->assertEquals('public', $data['visibility']);
        $this->assertEquals(0, $data['clickCount']);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{6,8}$/', $data['shortCode']);
    }

    public function testCreateUrlWithCustomAlias(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'mylink'
            ]
        );

        $data = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->assertEquals('mylink', $data['shortCode']);
    }

    public function testCreateUrlWithPrivateVisibility(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'visibility' => 'private'
            ]
        );

        $data = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->assertEquals('private', $data['visibility']);
    }

    public function testCreateUrlWithExpiration(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'expiration' => '1h'
            ]
        );

        $data = $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->assertArrayHasKey('expiresAt', $data);
        $this->assertNotNull($data['expiresAt']);
    }

    public function testCreateUrlWithInvalidUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => 'not-a-url']
        );

        $this->assertValidationError('originalUrl');
    }

    public function testCreateUrlWithEmptyUrl(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            ['originalUrl' => '']
        );

        $this->assertValidationError('originalUrl');
    }

    public function testCreateUrlWithTooShortCustomAlias(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'abc12'
            ]
        );

        $this->assertValidationError('customAlias');
    }

    public function testCreateUrlWithTooLongCustomAlias(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'toolong123'
            ]
        );

        $this->assertValidationError('customAlias');
    }

    public function testCreateUrlWithInvalidCharactersInCustomAlias(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'my-lnk'
            ]
        );

        $this->assertValidationError('customAlias');
    }

    public function testCreateUrlWithDuplicateCustomAlias(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'customAlias' => 'unique1'
            ]
        );

        $this->assertJsonResponse(Response::HTTP_CREATED);

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://another.com',
                'customAlias' => 'unique1'
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateUrlWithInvalidExpiration(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'expiration' => 'invalid'
            ]
        );

        $this->assertValidationError('expiration');
    }

    public function testCreateUrlWithInvalidVisibility(): void
    {
        $session = $this->createSession();

        $this->makeAuthenticatedRequest(
            'POST',
            '/api/urls',
            $session['jwtToken'],
            [
                'originalUrl' => 'https://example.com',
                'visibility' => 'invalid'
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
