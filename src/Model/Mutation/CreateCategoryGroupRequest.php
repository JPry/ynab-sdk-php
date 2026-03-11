<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class CreateCategoryGroupRequest implements RequestModel
{
	public function __construct(
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
