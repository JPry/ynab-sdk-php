<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class UpdateCategoryRequest implements RequestModel
{
	public function __construct(
		public string $id,
		public ?string $name = null,
		public ?string $note = null,
		public ?string $categoryGroupId = null,
		public ?int $goalTarget = null,
		public ?string $goalTargetDate = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'category' => array_filter([
				'name' => $this->name,
				'note' => $this->note,
				'category_group_id' => $this->categoryGroupId,
				'goal_target' => $this->goalTarget,
				'goal_target_date' => $this->goalTargetDate,
			], fn($v) => $v !== null),
		];
	}
}
