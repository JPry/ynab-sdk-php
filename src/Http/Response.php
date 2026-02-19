<?php

declare(strict_types=1);

namespace JPry\YNAB\Http;

final readonly class Response
{
    /** @param array<string,string> $headers */
    public function __construct(
        public int $statusCode,
        public array $headers,
        public string $body,
    ) {
    }

    /** @return array<string,mixed> */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }
}
