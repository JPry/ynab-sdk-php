<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;
use JPry\YNAB\Internal\HasId;

final readonly class MoneyMovementGroup implements Model
{
	use HasId;

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
		$id = ArrayReader::requiredString($row, 'id');
		if ($id === null) {
			return null;
		}

		return new self(
			id: $id,
			groupCreatedAt: (string) ($row['group_created_at'] ?? ''),
			month: (string) ($row['month'] ?? ''),
			note: ArrayReader::nullableString($row, 'note'),
			performedByUserId: ArrayReader::nullableString($row, 'performed_by_user_id'),
		);
	}
}
