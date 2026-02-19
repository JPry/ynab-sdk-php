<?php

declare(strict_types=1);

namespace JPry\YNAB\OAuth;

use GuzzleHttp\Psr7\Request;
use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\Http\RequestSender;

final readonly class OAuthClient
{
	public function __construct(
		private OAuthConfig $config,
		private RequestSender $requestSender,
	) {
	}

	public function authorizationUrl(string $state): string
	{
		$query = http_build_query([
			'client_id' => $this->config->clientId,
			'redirect_uri' => $this->config->redirectUri,
			'response_type' => 'code',
			'state' => $state,
		]);

		$authorizeUrl = rtrim($this->config->authorizeUrl, '?');

		return "{$authorizeUrl}?{$query}";
	}

	public function exchangeCodeForTokens(string $code): OAuthTokens
	{
		return $this->tokenRequest([
			'grant_type' => 'authorization_code',
			'code' => $code,
			'client_id' => $this->config->clientId,
			'client_secret' => $this->config->clientSecret,
			'redirect_uri' => $this->config->redirectUri,
		]);
	}

	public function refreshAccessToken(string $refreshToken): OAuthTokens
	{
		return $this->tokenRequest([
			'grant_type' => 'refresh_token',
			'refresh_token' => $refreshToken,
			'client_id' => $this->config->clientId,
			'client_secret' => $this->config->clientSecret,
		]);
	}

	/** @param array<string,string> $form */
	private function tokenRequest(array $form): OAuthTokens
	{
		$body = http_build_query($form);
		$response = $this->requestSender->sendRequest(
			new Request(
				'POST',
				$this->config->tokenUrl,
				[
					'Accept' => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				$body,
			),
		);

		$decoded = json_decode((string) $response->getBody(), true);
		if (!is_array($decoded)) {
			$decoded = [];
		}
		$tokens = OAuthTokens::fromArray($decoded);

		if ($tokens === null) {
			throw new YnabException('Could not parse OAuth token response.');
		}

		return $tokens;
	}
}
