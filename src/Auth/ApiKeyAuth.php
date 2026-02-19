<?php

declare(strict_types=1);

namespace JPry\YNAB\Auth;

final readonly class ApiKeyAuth implements AuthMethod
{
    public function __construct(private string $apiKey)
    {
    }

    public function apply(array $headers): array
    {
        $headers['Authorization'] = 'Bearer ' . $this->apiKey;

        return $headers;
    }
}
