<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class ImportTransactionsRequest implements RequestModel
{
	/** @param list<TransactionPayload> $transactions */
	public function __construct(
		public array $transactions,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'transactions' => array_map(
				static fn (TransactionPayload $transaction): array => $transaction->toArray(),
				$this->transactions,
			),
		];
	}
}
