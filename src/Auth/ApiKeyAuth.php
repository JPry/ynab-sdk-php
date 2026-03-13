<?php

declare(strict_types=1);

namespace JPry\YNAB\Auth;

use JPry\YNAB\Exception\InvalidStringException;

final readonly class ApiKeyAuth implements AuthMethod
{
	/** @throws InvalidStringException */
	public function __construct(private string $apiKey)
	{
		if (trim($this->apiKey) === '') {
			throw InvalidStringException::forEmptyString('apiKey');
		}
	}

	public function apply(array $headers): array
	{
		$headers['Authorization'] = "Bearer {$this->apiKey}";

		return $headers;
	}

	public function __debugInfo(): ?array
	{
		return [
			'apiKey' => 'REDACTED API key',
		];
	}
}
