<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Model;

final readonly class UpdateTransactionRequest implements RequestModel, Model
{
	public function __construct(
		public string $id,
		public TransactionPayload $transaction,
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
			'transaction' => $this->transaction->toArray(),
		];
	}
}
