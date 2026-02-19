<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class Transaction
{
    /** @param array<string,mixed> $raw */
    public function __construct(
        public string $id,
        public string $accountId,
        public ?string $date,
        public int $amount,
        public ?string $payeeName,
        public ?string $payeeId,
        public ?string $memo,
        public ?string $cleared,
        public ?bool $approved,
        public ?string $categoryId,
        public bool $isPending,
        public array $raw,
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
            accountId: (string) ($row['account_id'] ?? ''),
            date: isset($row['date']) ? (string) $row['date'] : null,
            amount: (int) ($row['amount'] ?? 0),
            payeeName: isset($row['payee_name']) ? (string) $row['payee_name'] : null,
            payeeId: isset($row['payee_id']) ? (string) $row['payee_id'] : null,
            memo: isset($row['memo']) ? (string) $row['memo'] : null,
            cleared: isset($row['cleared']) ? (string) $row['cleared'] : null,
            approved: array_key_exists('approved', $row) ? (bool) $row['approved'] : null,
            categoryId: isset($row['category_id']) ? (string) $row['category_id'] : null,
            isPending: (bool) ($row['is_pending'] ?? false),
            raw: $row,
        );
    }
}
