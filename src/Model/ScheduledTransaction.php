<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class ScheduledTransaction
{
	/** @param array<string,mixed> $raw */
	public function __construct(
		public string $id,
		public string $accountId,
		public ?string $dateFirst,
		public ?string $dateNext,
		public string $frequency,
		public int $amount,
		public ?string $memo,
		public ?string $flagColor,
		public ?string $accountName,
		public ?string $payeeName,
		public ?string $categoryName,
		public ?string $payeeId,
		public ?string $categoryId,
		public ?string $transferAccountId,
		public bool $deleted,
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
			dateFirst: isset($row['date_first']) ? (string) $row['date_first'] : null,
			dateNext: isset($row['date_next']) ? (string) $row['date_next'] : null,
			frequency: (string) ($row['frequency'] ?? ''),
			amount: (int) ($row['amount'] ?? 0),
			memo: isset($row['memo']) ? (string) $row['memo'] : null,
			flagColor: isset($row['flag_color']) ? (string) $row['flag_color'] : null,
			accountName: isset($row['account_name']) ? (string) $row['account_name'] : null,
			payeeName: isset($row['payee_name']) ? (string) $row['payee_name'] : null,
			categoryName: isset($row['category_name']) ? (string) $row['category_name'] : null,
			payeeId: isset($row['payee_id']) ? (string) $row['payee_id'] : null,
			categoryId: isset($row['category_id']) ? (string) $row['category_id'] : null,
			transferAccountId: isset($row['transfer_account_id']) ? (string) $row['transfer_account_id'] : null,
			deleted: (bool) ($row['deleted'] ?? false),
			raw: $row,
		);
	}
}
