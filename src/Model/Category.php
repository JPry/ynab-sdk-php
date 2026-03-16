<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class Category
{
	public function __construct(
		public string $id,
		public string $name,
		public string $groupId,
		public string $groupName,
		public int $groupOrder,
		public int $categoryOrder,
		public bool $hidden,
		public bool $deleted,
	) {
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row, ?array $groupContext = null): ?self
	{
		$id = ArrayReader::requiredString($row, 'id');
		if ($id === null) {
			return null;
		}

		$ctx = $groupContext ?? [];

		return new self(
			id: $id,
			name: (string) ($row['name'] ?? ''),
			groupId: (string) ($ctx['groupId'] ?? ''),
			groupName: (string) ($ctx['groupName'] ?? ''),
			groupOrder: ArrayReader::int($ctx, 'groupOrder'),
			categoryOrder: ArrayReader::int($ctx, 'categoryOrder'),
			hidden: ArrayReader::bool($row, 'hidden'),
			deleted: ArrayReader::bool($row, 'deleted'),
		);
	}
}
