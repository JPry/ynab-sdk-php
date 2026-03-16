<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class CategoryDetail implements Model
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
			categoryGroupId: (string) ($row['category_group_id'] ?? ''),
			categoryGroupName: (string) ($row['category_group_name'] ?? ''),
			name: (string) ($row['name'] ?? ''),
			note: ArrayReader::nullableString($row, 'note'),
			budgeted: ArrayReader::int($row, 'budgeted'),
			activity: ArrayReader::int($row, 'activity'),
			balance: ArrayReader::int($row, 'balance'),
			hidden: ArrayReader::bool($row, 'hidden'),
			deleted: ArrayReader::bool($row, 'deleted'),
			raw: $row,
		);
	}
}
