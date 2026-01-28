<?php

namespace App\Tests\Service;

use App\Entity\Url;
use App\Service\ShortUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ShortUrlGeneratorTest extends TestCase
{
    public function testGenerateShortCodeReturnsValidLength(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = new ShortUrlGenerator($em);
        $shortCode = $service->generateShortCode();

        $this->assertGreaterThanOrEqual(6, strlen($shortCode));
        $this->assertLessThanOrEqual(8, strlen($shortCode));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $shortCode);
    }

    public function testGenerateShortCodeIsUnique(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = new ShortUrlGenerator($em);
        $codes = [];

        for ($i = 0; $i < 10; $i++) {
            $codes[] = $service->generateShortCode();
        }

        $this->assertCount(10, array_unique($codes));
    }

    public function testValidateCustomAliasThrowsExceptionForShortAlias(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $service = new ShortUrlGenerator($em);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Alias must be between 6 and 8 characters');

        $service->validateCustomAlias('abc');
    }

    public function testValidateCustomAliasThrowsExceptionForLongAlias(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $service = new ShortUrlGenerator($em);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Alias must be between 6 and 8 characters');

        $service->validateCustomAlias('verylongalias');
    }

    public function testValidateCustomAliasThrowsExceptionForInvalidCharacters(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $service = new ShortUrlGenerator($em);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Alias must contain only alphanumeric characters');

        $service->validateCustomAlias('test-1');
    }

    public function testValidateCustomAliasThrowsExceptionForTakenAlias(): void
    {
        $existingUrl = $this->createMock(Url::class);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($existingUrl);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = new ShortUrlGenerator($em);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('This alias is already taken');

        $service->validateCustomAlias('mylink');
    }

    public function testValidateCustomAliasAcceptsValidAlias(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = new ShortUrlGenerator($em);
        $alias = $service->validateCustomAlias('mylink');

        $this->assertEquals('mylink', $alias);
    }
}
