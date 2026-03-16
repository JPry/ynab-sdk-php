<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class Payee implements Model
{
	public function __construct(
		public string $id,
		public string $name,
		public ?string $transferAccountId,
		public bool $deleted,
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
			name: (string) ($row['name'] ?? ''),
			transferAccountId: ArrayReader::nullableNonEmptyString($row, 'transfer_account_id'),
			deleted: ArrayReader::bool($row, 'deleted'),
		);
	}
}
