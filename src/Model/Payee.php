<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class Payee
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $transferAccountId,
        public bool $deleted,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromArray(array $row): ?self
    {
        $id = trim((string) ($row['id'] ?? ''));
        if ($id === '') {
            return null;
        }

        $transferAccountId = isset($row['transfer_account_id']) ? trim((string) $row['transfer_account_id']) : null;

        return new self(
            id: $id,
            name: (string) ($row['name'] ?? ''),
            transferAccountId: $transferAccountId !== '' ? $transferAccountId : null,
            deleted: (bool) ($row['deleted'] ?? false),
        );
    }
}
