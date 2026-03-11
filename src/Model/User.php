<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class User
{
	public function __construct(
		public string $id,
	) {
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$id = trim((string) ($row['id'] ?? ''));
		if ($id === '') {
			return null;
		}

		return new self($id);
	}
}
