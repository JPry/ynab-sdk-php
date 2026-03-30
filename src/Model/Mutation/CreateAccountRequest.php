<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Enum\SaveAccountType;

final readonly class CreateAccountRequest implements RequestModel
{
	public function __construct(
		public string $name,
		public SaveAccountType $type,
		public int $balance,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'account' => [
				'name' => $this->name,
				'type' => $this->type->value,
				'balance' => $this->balance,
			],
		];
	}
}
