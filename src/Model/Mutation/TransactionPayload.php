<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Enum\TransactionClearedStatus;
use JPry\YNAB\Model\Enum\TransactionFlagColor;

final readonly class TransactionPayload implements RequestModel
{
	/** @param list<SubTransactionPayload> $subtransactions */
	public function __construct(
		public ?string $accountId = null,
		public ?string $date = null,
		public ?int $amount = null,
		public ?string $payeeId = null,
		public ?string $payeeName = null,
		public ?string $categoryId = null,
		public ?string $memo = null,
		public ?TransactionClearedStatus $cleared = null,
		public ?bool $approved = null,
		public ?TransactionFlagColor $flagColor = null,
		public array $subtransactions = [],
		public ?string $importId = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$data = array_filter([
			'account_id' => $this->accountId,
			'date' => $this->date,
			'amount' => $this->amount,
			'payee_id' => $this->payeeId,
			'payee_name' => $this->payeeName,
			'category_id' => $this->categoryId,
			'memo' => $this->memo,
			'cleared' => $this->cleared?->value,
			'approved' => $this->approved,
			'flag_color' => $this->flagColor?->value,
			'import_id' => $this->importId,
		], fn ($v) => $v !== null);

		if ($this->subtransactions !== []) {
			$data['subtransactions'] = array_map(
				static fn (SubTransactionPayload $subtransaction): array => $subtransaction->toArray(),
				$this->subtransactions,
			);
		}

		return $data;
	}
}
