<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;
use JPry\YNAB\Internal\BudgetDeprecationWarningTrait;

/**
 * @deprecated YNAB API v1.79.0 renamed budgets to plans. Use Plan instead.
 */
final readonly class Budget implements Model
{
	use BudgetDeprecationWarningTrait;

	public function __construct(
		public string $id,
		public string $name,
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
			name: (string) ($row['name'] ?? 'Unnamed'),
		);
	}
}
