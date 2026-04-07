<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;
use JPry\YNAB\Internal\HasId;

final readonly class CategoryDetail implements Model
{
	use HasId;

	/** @param array<string,mixed> $raw */
	public function __construct(
		public string $id,
		public string $categoryGroupId,
		public string $categoryGroupName,
		public string $name,
		public ?string $note,
		public int $budgeted,
		public int $activity,
		public int $balance,
		public bool $hidden,
		public bool $deleted,
		public ?string $balanceFormatted,
		public ?float $balanceCurrency,
		public ?string $activityFormatted,
		public ?float $activityCurrency,
		public ?string $budgetedFormatted,
		public ?float $budgetedCurrency,
		public ?string $goalTargetFormatted,
		public ?float $goalTargetCurrency,
		public ?string $goalUnderFundedFormatted,
		public ?float $goalUnderFundedCurrency,
		public ?string $goalOverallFundedFormatted,
		public ?float $goalOverallFundedCurrency,
		public ?string $goalOverallLeftFormatted,
		public ?float $goalOverallLeftCurrency,
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
			categoryGroupId: (string) ($row['category_group_id'] ?? ''),
			categoryGroupName: (string) ($row['category_group_name'] ?? ''),
			name: (string) ($row['name'] ?? ''),
			note: ArrayReader::nullableString($row, 'note'),
			budgeted: ArrayReader::int($row, 'budgeted'),
			activity: ArrayReader::int($row, 'activity'),
			balance: ArrayReader::int($row, 'balance'),
			hidden: ArrayReader::bool($row, 'hidden'),
			deleted: ArrayReader::bool($row, 'deleted'),
			balanceFormatted: ArrayReader::nullableString($row, 'balance_formatted'),
			balanceCurrency: ArrayReader::nullableFloat($row, 'balance_currency'),
			activityFormatted: ArrayReader::nullableString($row, 'activity_formatted'),
			activityCurrency: ArrayReader::nullableFloat($row, 'activity_currency'),
			budgetedFormatted: ArrayReader::nullableString($row, 'budgeted_formatted'),
			budgetedCurrency: ArrayReader::nullableFloat($row, 'budgeted_currency'),
			goalTargetFormatted: ArrayReader::nullableString($row, 'goal_target_formatted'),
			goalTargetCurrency: ArrayReader::nullableFloat($row, 'goal_target_currency'),
			goalUnderFundedFormatted: ArrayReader::nullableString($row, 'goal_under_funded_formatted'),
			goalUnderFundedCurrency: ArrayReader::nullableFloat($row, 'goal_under_funded_currency'),
			goalOverallFundedFormatted: ArrayReader::nullableString($row, 'goal_overall_funded_formatted'),
			goalOverallFundedCurrency: ArrayReader::nullableFloat($row, 'goal_overall_funded_currency'),
			goalOverallLeftFormatted: ArrayReader::nullableString($row, 'goal_overall_left_formatted'),
			goalOverallLeftCurrency: ArrayReader::nullableFloat($row, 'goal_overall_left_currency'),
			raw: $row,
		);
	}
}
