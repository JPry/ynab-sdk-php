<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class CreateCategoryRequest implements RequestModel
{
	public function __construct(
		public string $name,
		public string $categoryGroupId,
		public ?string $note = null,
		public ?int $goalTarget = null,
		public ?string $goalTargetDate = null,
		public ?bool $goalNeedsWholeAmount = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$required = [
			'name' => $this->name,
			'category_group_id' => $this->categoryGroupId,
		];
		$optional = array_filter([
			'note' => $this->note,
			'goal_target' => $this->goalTarget,
			'goal_target_date' => $this->goalTargetDate,
			'goal_needs_whole_amount' => $this->goalNeedsWholeAmount,
		], fn ($v) => $v !== null);

		return [
			'category' => array_merge($required, $optional),
		];
	}
}
