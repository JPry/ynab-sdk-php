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
		$required = ['amount' => $this->amount];
		$optional = array_filter([
			'payee_id' => $this->payeeId,
			'payee_name' => $this->payeeName,
			'category_id' => $this->categoryId,
			'memo' => $this->memo,
		], fn($v) => $v !== null);

		return array_merge($required, $optional);
	}
}
