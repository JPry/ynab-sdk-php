<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class CreatePayeeRequest implements RequestModel
{
	public function __construct(
		public string $name,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'payee' => [
				'name' => $this->name,
			],
		];
	}
}
