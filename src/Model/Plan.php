<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

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
		$id = trim((string) ($row['id'] ?? ''));
		if ($id === '') {
			return null;
		}

		return new self(
			id: $id,
			name: (string) ($row['name'] ?? 'Unnamed'),
			lastModifiedOn: isset($row['last_modified_on']) ? (string) $row['last_modified_on'] : null,
			firstMonth: isset($row['first_month']) ? (string) $row['first_month'] : null,
			lastMonth: isset($row['last_month']) ? (string) $row['last_month'] : null,
			dateFormat: isset($row['date_format']) && is_array($row['date_format']) ? $row['date_format'] : null,
			currencyFormat: isset($row['currency_format']) && is_array($row['currency_format']) ? $row['currency_format'] : null,
			raw: $row,
		);
	}
}
