<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class MoneyMovement
{
	public function __construct(
		public string $id,
		public ?string $month,
		public ?string $movedAt,
		public ?string $note,
		public ?string $moneyMovementGroupId,
		public ?string $performedByUserId,
		public ?string $fromCategoryId,
		public ?string $toCategoryId,
		public int $amount,
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
			month: isset($row['month']) ? (string) $row['month'] : null,
			movedAt: isset($row['moved_at']) ? (string) $row['moved_at'] : null,
			note: isset($row['note']) ? (string) $row['note'] : null,
			moneyMovementGroupId: isset($row['money_movement_group_id']) ? (string) $row['money_movement_group_id'] : null,
			performedByUserId: isset($row['performed_by_user_id']) ? (string) $row['performed_by_user_id'] : null,
			fromCategoryId: isset($row['from_category_id']) ? (string) $row['from_category_id'] : null,
			toCategoryId: isset($row['to_category_id']) ? (string) $row['to_category_id'] : null,
			amount: (int) ($row['amount'] ?? 0),
		);
	}
}
