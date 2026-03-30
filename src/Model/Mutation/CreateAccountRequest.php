<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Enum\AccountType;
use JPry\YNAB\Model\Enum\SaveAccountType;

final readonly class CreateAccountRequest implements RequestModel
{
	public readonly SaveAccountType $saveType;

	/**
	 * @todo v3.0.0: Remove AccountType acceptance — only allow SaveAccountType.
	 */
	public function __construct(
		public string $name,
		public SaveAccountType|AccountType $type,
		public int $balance,
	) {
		$this->saveType = $type instanceof SaveAccountType
			? $type
			: SaveAccountType::from($type->value);
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'account' => [
				'name' => $this->name,
				'type' => $this->saveType->value,
				'balance' => $this->balance,
			],
		];
	}
}
