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
		$data = $this->transaction->toArray();

		if ($this->id !== null) {
			$data['id'] = $this->id;
		}
		if ($this->importId !== null) {
			$data['import_id'] = $this->importId;
		}

		return $data;
	}
}
