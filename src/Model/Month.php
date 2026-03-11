<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class Month
{
	public function __construct(
		public string $month,
		public ?string $note,
		public int $income,
		public int $budgeted,
		public int $activity,
		public int $toBeBudgeted,
		public ?int $ageOfMoney,
		public bool $deleted,
	) {
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$month = trim((string) ($row['month'] ?? ''));
		if ($month === '') {
			return null;
		}

		return new self(
			month: $month,
			note: isset($row['note']) ? (string) $row['note'] : null,
			income: (int) ($row['income'] ?? 0),
			budgeted: (int) ($row['budgeted'] ?? 0),
			activity: (int) ($row['activity'] ?? 0),
			toBeBudgeted: (int) ($row['to_be_budgeted'] ?? 0),
			ageOfMoney: array_key_exists('age_of_money', $row) && $row['age_of_money'] !== null ? (int) $row['age_of_money'] : null,
			deleted: (bool) ($row['deleted'] ?? false),
		);
	}
}
