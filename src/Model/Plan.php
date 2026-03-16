<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class Plan
{
	/**
	 * @param array<string,mixed>|null $dateFormat
	 * @param array<string,mixed>|null $currencyFormat
	 * @param array<string,mixed> $raw
	 */
	public function __construct(
		public string $id,
		public string $name,
		public ?string $lastModifiedOn,
		public ?string $firstMonth,
		public ?string $lastMonth,
		public ?array $dateFormat,
		public ?array $currencyFormat,
		public array $raw,
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
			name: (string) ($row['name'] ?? 'Unnamed'),
			lastModifiedOn: ArrayReader::nullableString($row, 'last_modified_on'),
			firstMonth: ArrayReader::nullableString($row, 'first_month'),
			lastMonth: ArrayReader::nullableString($row, 'last_month'),
			dateFormat: ArrayReader::nullableArray($row, 'date_format'),
			currencyFormat: ArrayReader::nullableArray($row, 'currency_format'),
			raw: $row,
		);
	}
}
