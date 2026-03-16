<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class ScheduledTransaction implements Model
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

	public function getId(): string
	{
		return $this->id;
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$id = ArrayReader::requiredString($row, 'id');
		if ($id === null) {
			return null;
		}

		return new self(
			id: $id,
			accountId: (string) ($row['account_id'] ?? ''),
			dateFirst: ArrayReader::nullableString($row, 'date_first'),
			dateNext: ArrayReader::nullableString($row, 'date_next'),
			frequency: (string) ($row['frequency'] ?? ''),
			amount: ArrayReader::int($row, 'amount'),
			memo: ArrayReader::nullableString($row, 'memo'),
			flagColor: ArrayReader::nullableString($row, 'flag_color'),
			accountName: ArrayReader::nullableString($row, 'account_name'),
			payeeName: ArrayReader::nullableString($row, 'payee_name'),
			categoryName: ArrayReader::nullableString($row, 'category_name'),
			payeeId: ArrayReader::nullableString($row, 'payee_id'),
			categoryId: ArrayReader::nullableString($row, 'category_id'),
			transferAccountId: ArrayReader::nullableString($row, 'transfer_account_id'),
			deleted: ArrayReader::bool($row, 'deleted'),
			raw: $row,
		);
	}
}
