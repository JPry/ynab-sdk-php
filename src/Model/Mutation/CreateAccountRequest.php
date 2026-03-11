<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class CreateAccountRequest implements RequestModel
{
	public function __construct(
		public string $name,
		public string $type,
		public int $balance,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'account' => [
				'name' => $this->name,
				'type' => $this->type,
				'balance' => $this->balance,
			],
		];
	}
}
