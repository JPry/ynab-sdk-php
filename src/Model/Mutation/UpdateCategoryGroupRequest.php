<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class UpdateCategoryGroupRequest implements RequestModel
{
	public function __construct(
		public string $id,
		public string $name,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'category_group' => [
				'name' => $this->name,
			],
		];
	}
}
