<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class UpdateTransactionRequest implements RequestModel
{
	public function __construct(
		public string $id,
		public TransactionPayload $transaction,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'transaction' => $this->transaction->toArray(),
		];
	}
}
