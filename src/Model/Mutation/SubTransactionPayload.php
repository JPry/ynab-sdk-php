<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

final readonly class SubTransactionPayload implements RequestModel
{
	public function __construct(
		public int $amount,
		public ?string $payeeId = null,
		public ?string $payeeName = null,
		public ?string $categoryId = null,
		public ?string $memo = null,
	) {
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		$data = [
			'amount' => $this->amount,
		];

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

		return $data;
	}
}
