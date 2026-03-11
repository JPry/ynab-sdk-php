<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class CreateTransactionsRequest implements RequestModel
{
	/** @param list<TransactionPayload> $transactions */
	public function __construct(
		public ?TransactionPayload $transaction = null,
		public array $transactions = [],
	) {
	}

	public static function single(TransactionPayload $transaction): self
	{
		return new self(transaction: $transaction);
	}

	/** @param list<TransactionPayload> $transactions */
	public static function multiple(array $transactions): self
	{
		return new self(transactions: $transactions);
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		if ($this->transaction !== null) {
			return [
				'transaction' => $this->transaction->toArray(),
			];
		}

		return [
			'transactions' => array_map(
				static fn (TransactionPayload $transaction): array => $transaction->toArray(),
				$this->transactions,
			),
		];
	}
}
