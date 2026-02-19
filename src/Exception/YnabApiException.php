<?php

declare(strict_types=1);

namespace JPry\YNAB\Exception;

use Throwable;

final class YnabApiException extends YnabException
{
	public function __construct(
		string $message,
		public readonly ?int $statusCode = null,
		public readonly ?int $retryAfterSeconds = null,
		public readonly ?string $errorId = null,
		public readonly ?string $errorName = null,
		public readonly ?string $errorDetail = null,
		?Throwable $previous = null,
	) {
		parent::__construct($message, $statusCode ?? 0, $previous);
	}

	public function identifier(): ?string
	{
		$parts = array_values(array_filter([
			$this->errorId,
			$this->errorName,
		], static fn (?string $v): bool => $v !== null && $v !== ''));

		return $parts === [] ? null : implode(' ', $parts);
	}
}
