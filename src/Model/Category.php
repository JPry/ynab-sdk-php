<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

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
		$id = trim((string) ($row['id'] ?? ''));
		if ($id === '') {
			return null;
		}

		return new self(
			id: $id,
			name: (string) ($row['name'] ?? ''),
			groupId: (string) ($groupContext['groupId'] ?? ''),
			groupName: (string) ($groupContext['groupName'] ?? ''),
			groupOrder: (int) ($groupContext['groupOrder'] ?? 0),
			categoryOrder: (int) ($groupContext['categoryOrder'] ?? 0),
			hidden: (bool) ($row['hidden'] ?? false),
			deleted: (bool) ($row['deleted'] ?? false),
		);
	}
}
