<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Internal\HasId;
use JPry\YNAB\Model\Model;

final readonly class UpdatePayeeRequest implements RequestModel, Model
{
	use HasId;

	public function __construct(
		public string $id,
		public ?string $name = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'payee' => array_filter([
				'name' => $this->name,
			], fn ($v) => $v !== null),
		];
	}
}
