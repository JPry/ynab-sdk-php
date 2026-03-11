<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class CategoryDetail
{
	/** @param array<string,mixed> $raw */
	public function __construct(
		public string $id,
		public string $categoryGroupId,
		public string $categoryGroupName,
		public string $name,
		public ?string $note,
		public int $budgeted,
		public int $activity,
		public int $balance,
		public bool $hidden,
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
			categoryGroupId: (string) ($row['category_group_id'] ?? ''),
			categoryGroupName: (string) ($row['category_group_name'] ?? ''),
			name: (string) ($row['name'] ?? ''),
			note: isset($row['note']) ? (string) $row['note'] : null,
			budgeted: (int) ($row['budgeted'] ?? 0),
			activity: (int) ($row['activity'] ?? 0),
			balance: (int) ($row['balance'] ?? 0),
			hidden: (bool) ($row['hidden'] ?? false),
			deleted: (bool) ($row['deleted'] ?? false),
			raw: $row,
		);
	}
}
