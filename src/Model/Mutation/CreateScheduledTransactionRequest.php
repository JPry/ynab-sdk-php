<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class CreateScheduledTransactionRequest implements RequestModel
{
	public function __construct(
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
