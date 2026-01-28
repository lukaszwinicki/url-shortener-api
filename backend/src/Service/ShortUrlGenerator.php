<?php

namespace App\Service;

use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class ShortUrlGenerator
{
    private const BASE62_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const MIN_LENGTH = 6;
    private const MAX_LENGTH = 8;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function generateShortCode(): string
    {
        $attempts = 0;
        $maxAttempts = 10;

        do {
            $length = $attempts < 5 ? self::MIN_LENGTH : self::MIN_LENGTH + ($attempts - 5);
            $length = min($length, self::MAX_LENGTH);

            $shortCode = $this->generateRandomBase62($length);

            $exists = $this->entityManager
                ->getRepository(Url::class)
                ->findOneBy(['shortCode' => $shortCode]);

            if (!$exists) {
                return $shortCode;
            }

            $attempts++;
        } while ($attempts < $maxAttempts);

        throw new ServiceUnavailableHttpException(null, 'Failed to generate unique short code after ' . $maxAttempts . ' attempts');
    }

    private function generateRandomBase62(int $length): string
    {
        $code = '';
        $charsLength = strlen(self::BASE62_CHARS);

        for ($i = 0; $i < $length; $i++) {
            $code .= self::BASE62_CHARS[random_int(0, $charsLength - 1)];
        }

        return $code;
    }

    public function validateCustomAlias(string $alias): string
    {
        if (strlen($alias) < self::MIN_LENGTH || strlen($alias) > self::MAX_LENGTH) {
            throw new BadRequestHttpException(
                sprintf('Alias must be between %d and %d characters', self::MIN_LENGTH, self::MAX_LENGTH)
            );
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $alias)) {
            throw new BadRequestHttpException('Alias must contain only alphanumeric characters');
        }

        $exists = $this->entityManager
            ->getRepository(Url::class)
            ->findOneBy(['shortCode' => $alias]);

        if ($exists) {
            throw new BadRequestHttpException('This alias is already taken');
        }

        return $alias;
    }
}
