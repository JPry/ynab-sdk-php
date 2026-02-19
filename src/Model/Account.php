<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class Account
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public bool $closed,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromArray(array $row): ?self
    {
        $id = trim((string) ($row['id'] ?? ''));
        if ($id === '') {
            return null;
        }

        return new self(
            id: $id,
            name: (string) ($row['name'] ?? ''),
            type: (string) ($row['type'] ?? ''),
            closed: (bool) ($row['closed'] ?? false),
        );
    }
}
