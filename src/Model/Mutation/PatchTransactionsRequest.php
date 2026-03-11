<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class PatchTransactionsRequest implements RequestModel
{
	/** @param list<PatchTransactionPayload> $transactions */
	public function __construct(
		public array $transactions,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'transactions' => array_map(
				static fn (PatchTransactionPayload $transaction): array => $transaction->toArray(),
				$this->transactions,
			),
		];
	}
}
