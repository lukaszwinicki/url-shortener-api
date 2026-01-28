<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends KernelTestCase
{
    protected ?Response $response = null;

    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);
        $this->clearDatabase();
    }

    private function clearDatabase(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();

        $connection->executeStatement('TRUNCATE TABLE url_logs CASCADE');
        $connection->executeStatement('TRUNCATE TABLE urls CASCADE');
        $connection->executeStatement('TRUNCATE TABLE sessions CASCADE');
    }

    protected function request(string $method, string $uri, array $headers = [], ?string $content = null): void
    {
        $headers['Accept'] = $headers['Accept'] ?? 'application/json';

        $request = Request::create($uri, $method, [], [], [], $this->transformHeadersToServer($headers), $content);
        $this->response = self::$kernel->handle($request);
    }

    private function transformHeadersToServer(array $headers): array
    {
        $server = [];
        foreach ($headers as $key => $value) {
            if ($key === 'CONTENT_TYPE') {
                $server['CONTENT_TYPE'] = $value;
            } else {
                $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
            }
        }
        return $server;
    }

    protected function getResponse(): Response
    {
        if ($this->response === null) {
            throw new \RuntimeException('No request has been made yet');
        }
        return $this->response;
    }

    protected function assertResponseIsSuccessful(): void
    {
        $statusCode = $this->getResponse()->getStatusCode();
        $this->assertGreaterThanOrEqual(200, $statusCode);
        $this->assertLessThan(300, $statusCode);
    }

    protected function assertResponseStatusCodeSame(int $expectedCode): void
    {
        $this->assertEquals($expectedCode, $this->getResponse()->getStatusCode());
    }

    protected function createSession(): array
    {
        $this->request('POST', '/api/session');

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->getResponse()->getContent(), true);

        $this->assertArrayHasKey('jwtToken', $response);
        $this->assertNotNull($response['jwtToken']);

        return $response;
    }

    protected function makeAuthenticatedRequest(
        string $method,
        string $uri,
        string $jwtToken,
        array $data = []
    ): void {
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'Authorization' => 'Bearer ' . $jwtToken,
        ];

        $this->request(
            $method,
            $uri,
            $headers,
            $data ? json_encode($data) : null
        );
    }

    protected function assertJsonResponse(int $expectedStatusCode = Response::HTTP_OK): array
    {
        $response = $this->getResponse();
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('json', $contentType, 'Response Content-Type should contain json');

        return json_decode($response->getContent(), true);
    }

    protected function assertValidationError(string $field): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = json_decode($this->getResponse()->getContent(), true);

        $this->assertArrayHasKey('violations', $data);
        $fieldFound = false;

        foreach ($data['violations'] as $violation) {
            if ($violation['propertyPath'] === $field) {
                $fieldFound = true;
                break;
            }
        }

        $this->assertTrue($fieldFound, "Expected validation error for field: {$field}");
    }

    protected function assertResponseRedirects(?string $expectedLocation = null, int $expectedCode = Response::HTTP_FOUND): void
    {
        $response = $this->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertTrue(
            $statusCode >= 300 && $statusCode < 400,
            sprintf('Expected redirect status code (3xx), got %d', $statusCode)
        );

        if ($expectedCode !== null) {
            $this->assertEquals($expectedCode, $statusCode);
        }

        if ($expectedLocation !== null) {
            $location = $response->headers->get('Location');
            $this->assertEquals($expectedLocation, $location);
        }
    }
}
