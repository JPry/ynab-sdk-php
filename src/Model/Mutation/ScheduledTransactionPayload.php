<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

use JPry\YNAB\Model\Enum\ScheduledTransactionFrequency;
use JPry\YNAB\Model\Enum\TransactionFlagColor;

final readonly class ScheduledTransactionPayload implements RequestModel
{
	public function __construct(
		public string $accountId,
		public string $date,
		public ?int $amount = null,
		public ?string $payeeId = null,
		public ?string $payeeName = null,
		public ?string $categoryId = null,
		public ?string $memo = null,
		public ?TransactionFlagColor $flagColor = null,
		public ?ScheduledTransactionFrequency $frequency = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$required = [
			'account_id' => $this->accountId,
			'date' => $this->date,
		];
		$optional = array_filter([
			'amount' => $this->amount,
			'payee_id' => $this->payeeId,
			'payee_name' => $this->payeeName,
			'category_id' => $this->categoryId,
			'memo' => $this->memo,
			'flag_color' => $this->flagColor?->value,
			'frequency' => $this->frequency?->value,
		], fn($v) => $v !== null);

		return array_merge($required, $optional);
	}
}
