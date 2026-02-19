<?php

declare(strict_types=1);

namespace JPry\YNAB\Http;

final readonly class Request
{
    /** @param array<string,string> $headers */
    /** @param array<string,scalar|null> $query */
    /** @param array<string,mixed>|null $json */
    /** @param array<string,scalar|null>|null $form */
    public function __construct(
        public string $method,
        public string $url,
        public array $headers = [],
        public array $query = [],
        public ?array $json = null,
        public ?array $form = null,
    ) {
    }
}
