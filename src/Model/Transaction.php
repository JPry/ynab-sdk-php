<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;
use JPry\YNAB\Internal\HasId;

final readonly class Transaction implements Model
{
	use HasId;

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
		public array $raw,
	) {
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
			date: ArrayReader::nullableString($row, 'date'),
			amount: ArrayReader::int($row, 'amount'),
			payeeName: ArrayReader::nullableString($row, 'payee_name'),
			payeeId: ArrayReader::nullableString($row, 'payee_id'),
			memo: ArrayReader::nullableString($row, 'memo'),
			cleared: ArrayReader::nullableString($row, 'cleared'),
			approved: ArrayReader::nullableBool($row, 'approved'),
			categoryId: ArrayReader::nullableString($row, 'category_id'),
			raw: $row,
		);
	}
}
