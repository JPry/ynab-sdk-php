<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

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
		public ?string $cleared = null,
		public ?bool $approved = null,
		public ?string $flagColor = null,
		public array $subtransactions = [],
		public ?string $importId = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$data = [];

		if ($this->accountId !== null) {
			$data['account_id'] = $this->accountId;
		}
		if ($this->date !== null) {
			$data['date'] = $this->date;
		}
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
		if ($this->cleared !== null) {
			$data['cleared'] = $this->cleared;
		}
		if ($this->approved !== null) {
			$data['approved'] = $this->approved;
		}
		if ($this->flagColor !== null) {
			$data['flag_color'] = $this->flagColor;
		}
		if ($this->importId !== null) {
			$data['import_id'] = $this->importId;
		}
		if ($this->subtransactions !== []) {
			$data['subtransactions'] = array_map(
				static fn (SubTransactionPayload $subtransaction): array => $subtransaction->toArray(),
				$this->subtransactions,
			);
		}

		return $data;
	}
}
