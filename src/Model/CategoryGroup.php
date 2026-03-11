<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class CategoryGroup
{
	public function __construct(
		public string $id,
		public string $name,
		public bool $hidden,
		public bool $deleted,
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
			name: (string) ($row['name'] ?? ''),
			hidden: (bool) ($row['hidden'] ?? false),
			deleted: (bool) ($row['deleted'] ?? false),
		);
	}
}
