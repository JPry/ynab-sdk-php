<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class UpdatePayeeRequest implements RequestModel
{
	public function __construct(
		public string $id,
		public ?string $name = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$data = [];
		if ($this->name !== null) {
			$data['name'] = $this->name;
		}

		return [
			'payee' => $data,
		];
	}
}
