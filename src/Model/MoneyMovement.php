<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;
use JPry\YNAB\Internal\HasId;

final readonly class MoneyMovement implements Model
{
	use HasId;

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
		$id = ArrayReader::requiredString($row, 'id');
		if ($id === null) {
			return null;
		}

		return new self(
			id: $id,
			month: ArrayReader::nullableString($row, 'month'),
			movedAt: ArrayReader::nullableString($row, 'moved_at'),
			note: ArrayReader::nullableString($row, 'note'),
			moneyMovementGroupId: ArrayReader::nullableString($row, 'money_movement_group_id'),
			performedByUserId: ArrayReader::nullableString($row, 'performed_by_user_id'),
			fromCategoryId: ArrayReader::nullableString($row, 'from_category_id'),
			toCategoryId: ArrayReader::nullableString($row, 'to_category_id'),
			amount: ArrayReader::int($row, 'amount'),
		);
	}
}
