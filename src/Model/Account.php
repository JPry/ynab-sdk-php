<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class Account
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

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$id = trim((string) ($row['id'] ?? ''));
		if ($id === '') {
			return null;
		}

		$transferPayeeId = isset($row['transfer_payee_id']) ? trim((string) $row['transfer_payee_id']) : null;

		return new self(
			id: $id,
			name: (string) ($row['name'] ?? ''),
			type: (string) ($row['type'] ?? ''),
			closed: (bool) ($row['closed'] ?? false),
			balance: (int) ($row['balance'] ?? 0),
			clearedBalance: (int) ($row['cleared_balance'] ?? 0),
			unclearedBalance: (int) ($row['uncleared_balance'] ?? 0),
			onBudget: (bool) ($row['on_budget'] ?? false),
			deleted: (bool) ($row['deleted'] ?? false),
			note: isset($row['note']) ? (string) $row['note'] : null,
			transferPayeeId: $transferPayeeId !== '' ? $transferPayeeId : null,
			directImportLinked: array_key_exists('direct_import_linked', $row) ? (bool) $row['direct_import_linked'] : null,
			directImportInError: array_key_exists('direct_import_in_error', $row) ? (bool) $row['direct_import_in_error'] : null,
			lastReconciledAt: isset($row['last_reconciled_at']) ? (string) $row['last_reconciled_at'] : null,
			debtOriginalBalance: array_key_exists('debt_original_balance', $row) && $row['debt_original_balance'] !== null ? (int) $row['debt_original_balance'] : null,
			raw: $row,
		);
	}
}
