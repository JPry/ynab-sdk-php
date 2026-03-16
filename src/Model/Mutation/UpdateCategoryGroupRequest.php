<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Model;

final readonly class UpdateCategoryGroupRequest implements RequestModel, Model
{
	public function __construct(
		public string $id,
		public string $name,
	) {
	}

	public function getId(): string
	{
		return $this->id;
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
