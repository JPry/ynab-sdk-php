<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

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
		public ?string $flagColor = null,
		public ?string $frequency = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$data = [
			'account_id' => $this->accountId,
			'date' => $this->date,
		];

		if ($this->amount !== null) {
			$data['amount'] = $this->amount;
		}
		if ($this->payeeId !== null) {
			$data['payee_id'] = $this->payeeId;
		}
		if ($this->payeeName !== null) {
			$data['payee_name'] = $this->payeeName;
		}
		if ($this->categoryId !== null) {
			$data['category_id'] = $this->categoryId;
		}
		if ($this->memo !== null) {
			$data['memo'] = $this->memo;
		}
		if ($this->flagColor !== null) {
			$data['flag_color'] = $this->flagColor;
		}
		if ($this->frequency !== null) {
			$data['frequency'] = $this->frequency;
		}

		return $data;
	}
}
