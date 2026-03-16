<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Internal\HasId;
use JPry\YNAB\Model\Model;

final readonly class UpdateMonthCategoryRequest implements RequestModel, Model
{
	use HasId;

	public function __construct(
		public string $id,
		public int $budgeted,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'category' => [
				'budgeted' => $this->budgeted,
			],
		];
	}
}
