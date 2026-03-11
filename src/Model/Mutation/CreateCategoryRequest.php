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
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$data = [
			'name' => $this->name,
			'category_group_id' => $this->categoryGroupId,
		];

		if ($this->note !== null) {
			$data['note'] = $this->note;
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
