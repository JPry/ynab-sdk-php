<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Model;

final readonly class UpdatePayeeRequest implements RequestModel, Model
{
	public function __construct(
		public string $id,
		public ?string $name = null,
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
			'payee' => array_filter([
				'name' => $this->name,
			], fn($v) => $v !== null),
		];
	}
}
