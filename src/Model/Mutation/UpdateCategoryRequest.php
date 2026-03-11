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
		$data = [];

		if ($this->name !== null) {
			$data['name'] = $this->name;
		}
		if ($this->note !== null) {
			$data['note'] = $this->note;
		}
		if ($this->categoryGroupId !== null) {
			$data['category_group_id'] = $this->categoryGroupId;
		}
		if ($this->goalTarget !== null) {
			$data['goal_target'] = $this->goalTarget;
		}
		if ($this->goalTargetDate !== null) {
			$data['goal_target_date'] = $this->goalTargetDate;
		}

		return [
			'category' => $data,
		];
	}
}
