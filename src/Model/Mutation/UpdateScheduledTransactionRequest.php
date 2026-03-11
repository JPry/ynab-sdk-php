<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class UpdateScheduledTransactionRequest implements RequestModel
{
	public function __construct(
		public string $id,
		public ScheduledTransactionPayload $scheduledTransaction,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'scheduled_transaction' => $this->scheduledTransaction->toArray(),
		];
	}
}
