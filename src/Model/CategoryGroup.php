<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;
use JPry\YNAB\Internal\HasId;

final readonly class CategoryGroup implements Model
{
	use HasId;

	public function __construct(
		public string $id,
		public string $name,
		public bool $hidden,
		public bool $deleted,
		public bool $internal = false,
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
			name: (string) ($row['name'] ?? ''),
			hidden: ArrayReader::bool($row, 'hidden'),
			internal: ArrayReader::bool($row, 'internal'),
			deleted: ArrayReader::bool($row, 'deleted'),
		);
	}
}
