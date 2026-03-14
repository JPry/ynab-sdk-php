<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\BudgetDeprecationWarningTrait;

/**
 * @deprecated YNAB API v1.79.0 renamed budgets to plans. Use Plan instead.
 */
final readonly class Budget
{
	use BudgetDeprecationWarningTrait;

	public function __construct(
		public string $id,
		public string $name,
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
		);
	}
}
