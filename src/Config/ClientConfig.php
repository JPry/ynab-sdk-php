<?php

declare(strict_types=1);

namespace JPry\YNAB\Config;

final readonly class ClientConfig
{
	public function __construct(
		public string $baseUrl = 'https://api.ynab.com/v1',
		public int $timeoutSeconds = 30,
		public int $maxRetries = 2,
		public bool $allowInsecure = false,
		public int $maxPages = 100,
	) {
		$this->assertValidBaseUrl($this->baseUrl, $this->allowInsecure);
	}

	private function assertValidBaseUrl(string $baseUrl, bool $allowInsecure): void
	{
		if (str_starts_with($baseUrl, 'https://')) {
			return;
		}

		if ($allowInsecure && str_starts_with($baseUrl, 'http://')) {
			return;
		}

		$message = $allowInsecure
			? 'ClientConfig baseUrl must start with http:// or https://.'
			: 'ClientConfig baseUrl must start with https://. Pass allowInsecure: true only for local testing.';

		throw new \InvalidArgumentException($message);
	}
}
