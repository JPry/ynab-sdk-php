<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class MoneyMovementGroup
{
	public function __construct(
		public string $id,
		public string $groupCreatedAt,
		public string $month,
		public ?string $note,
		public ?string $performedByUserId,
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
			groupCreatedAt: (string) ($row['group_created_at'] ?? ''),
			month: (string) ($row['month'] ?? ''),
			note: isset($row['note']) ? (string) $row['note'] : null,
			performedByUserId: isset($row['performed_by_user_id']) ? (string) $row['performed_by_user_id'] : null,
		);
	}
}
