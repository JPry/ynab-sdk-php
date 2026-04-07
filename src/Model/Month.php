<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

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
		public ?string $incomeFormatted,
		public ?float $incomeCurrency,
		public ?string $budgetedFormatted,
		public ?float $budgetedCurrency,
		public ?string $activityFormatted,
		public ?float $activityCurrency,
		public ?string $toBeBudgetedFormatted,
		public ?float $toBeBudgetedCurrency,
		public ?array $categories,
	) {
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$month = ArrayReader::requiredString($row, 'month');
		if ($month === null) {
			return null;
		}

		return new self(
			month: $month,
			note: ArrayReader::nullableString($row, 'note'),
			income: ArrayReader::int($row, 'income'),
			budgeted: ArrayReader::int($row, 'budgeted'),
			activity: ArrayReader::int($row, 'activity'),
			toBeBudgeted: ArrayReader::int($row, 'to_be_budgeted'),
			ageOfMoney: ArrayReader::nullableInt($row, 'age_of_money'),
			deleted: ArrayReader::bool($row, 'deleted'),
			incomeFormatted: ArrayReader::nullableString($row, 'income_formatted'),
			incomeCurrency: ArrayReader::nullableFloat($row, 'income_currency'),
			budgetedFormatted: ArrayReader::nullableString($row, 'budgeted_formatted'),
			budgetedCurrency: ArrayReader::nullableFloat($row, 'budgeted_currency'),
			activityFormatted: ArrayReader::nullableString($row, 'activity_formatted'),
			activityCurrency: ArrayReader::nullableFloat($row, 'activity_currency'),
			toBeBudgetedFormatted: ArrayReader::nullableString($row, 'to_be_budgeted_formatted'),
			toBeBudgetedCurrency: ArrayReader::nullableFloat($row, 'to_be_budgeted_currency'),
			categories: ArrayReader::nullableArray($row, 'categories'),
		);
	}
}
