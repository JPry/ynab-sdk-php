<?php

declare(strict_types=1);

namespace JPry\YNAB\OAuth;

final readonly class OAuthConfig
{
	public function __construct(
		public string $clientId,
		public string $clientSecret,
		public string $redirectUri,
		public string $authorizeUrl = 'https://app.ynab.com/oauth/authorize',
		public string $tokenUrl = 'https://app.ynab.com/oauth/token',
	) {
	}

	public function __debugInfo(): ?array
	{
		return [
			'clientId' => 'REDACTED client ID',
			'clientSecret' => 'REDACTED client secret',
			'redirectUri' => $this->redirectUri,
			'authorizeUrl' => $this->authorizeUrl,
			'tokenUrl' => $this->tokenUrl,
		];
	}
}
