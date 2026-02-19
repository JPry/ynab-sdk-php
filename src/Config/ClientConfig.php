<?php

declare(strict_types=1);

namespace JPry\YNAB\Config;

final readonly class ClientConfig
{
    public function __construct(
        public string $baseUrl = 'https://api.ynab.com/v1',
        public int $timeoutSeconds = 30,
        public int $maxRetries = 2,
    ) {
    }
}
