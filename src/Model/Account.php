<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class Account implements Model
{
	/** @param array<string,mixed> $raw */
	public function __construct(
		public string $id,
		public string $name,
		public string $type,
		public bool $closed,
		public int $balance,
		public int $clearedBalance,
		public int $unclearedBalance,
		public bool $onBudget,
		public bool $deleted,
		public ?string $note,
		public ?string $transferPayeeId,
		public ?bool $directImportLinked,
		public ?bool $directImportInError,
		public ?string $lastReconciledAt,
		public ?int $debtOriginalBalance,
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
			name: (string) ($row['name'] ?? ''),
			type: (string) ($row['type'] ?? ''),
			closed: ArrayReader::bool($row, 'closed'),
			balance: ArrayReader::int($row, 'balance'),
			clearedBalance: ArrayReader::int($row, 'cleared_balance'),
			unclearedBalance: ArrayReader::int($row, 'uncleared_balance'),
			onBudget: ArrayReader::bool($row, 'on_budget'),
			deleted: ArrayReader::bool($row, 'deleted'),
			note: ArrayReader::nullableString($row, 'note'),
			transferPayeeId: ArrayReader::nullableNonEmptyString($row, 'transfer_payee_id'),
			directImportLinked: ArrayReader::nullableBool($row, 'direct_import_linked'),
			directImportInError: ArrayReader::nullableBool($row, 'direct_import_in_error'),
			lastReconciledAt: ArrayReader::nullableString($row, 'last_reconciled_at'),
			debtOriginalBalance: ArrayReader::nullableInt($row, 'debt_original_balance'),
			raw: $row,
		);
	}
}
