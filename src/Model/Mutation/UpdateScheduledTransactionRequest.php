<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Internal\HasId;
use JPry\YNAB\Model\Model;

final readonly class UpdateScheduledTransactionRequest implements RequestModel, Model
{
	use HasId;

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
