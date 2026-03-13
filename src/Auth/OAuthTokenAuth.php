<?php

declare(strict_types=1);

namespace JPry\YNAB\Auth;

use JPry\YNAB\Exception\InvalidStringException;

final class OAuthTokenAuth implements AuthMethod
{
	/** @var null|callable():string */
	private $refreshAccessToken;

	/** @throws InvalidStringException */
	public function __construct(
		private string $accessToken,
		?callable $refreshAccessToken = null,
	) {
		if (trim($this->accessToken) === '') {
			throw InvalidStringException::forEmptyString('accessToken');
		}

		$this->refreshAccessToken = $refreshAccessToken;
	}

	public function apply(array $headers): array
	{
		$headers['Authorization'] = "Bearer {$this->accessToken}";

		return $headers;
	}

	public function rotateToken(): ?string
	{
		if ($this->refreshAccessToken === null) {
			return null;
		}

		$token = trim((string) ($this->refreshAccessToken)());
		if ($token === '') {
			return null;
		}

		$this->accessToken = $token;

		return $this->accessToken;
	}

	public function __debugInfo(): ?array
	{
		return [
			'accessToken' => 'REDACTED access token',
			'refreshAccessToken' => $this->refreshAccessToken === null ? null : 'REDACTED refresh token',
		];
	}
}
