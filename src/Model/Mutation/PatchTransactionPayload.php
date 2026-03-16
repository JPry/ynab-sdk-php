<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class PatchTransactionPayload implements RequestModel
{
	public function __construct(
		public TransactionPayload $transaction,
		public ?string $id = null,
		public ?string $importId = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$optional = array_filter([
			'id' => $this->id,
			'import_id' => $this->importId,
		], fn($v) => $v !== null);

		return array_merge($this->transaction->toArray(), $optional);
	}
}
