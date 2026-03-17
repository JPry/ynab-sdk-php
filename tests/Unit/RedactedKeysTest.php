<?php

declare(strict_types=1);

use JPry\YNAB\Auth\ApiKeyAuth;
use JPry\YNAB\Auth\OAuthTokenAuth;
use JPry\YNAB\Exception\InvalidStringException;
use JPry\YNAB\OAuth\OAuthConfig;
use JPry\YNAB\OAuth\OAuthTokens;

it(
	'Prevents ApiKeyAuth::apiKey property from being shown in var_dump()',
	function () {
		$apiKey = 'foobar123';
		$auth = new ApiKeyAuth($apiKey);

		ob_start();
		var_dump($auth);
		$output = ob_get_clean();

		expect($output)->toBeString();
		expect($output)->not->toContain($apiKey);
		expect($output)->toContain('REDACTED API key');
	}
);

it(
	'Prevents ApiKeyAuth::apiKey property from being shown in print_r()',
	function () {
		$apiKey = 'foobar123';
		$auth = new ApiKeyAuth($apiKey);

		$output = print_r($auth, true);

		expect($output)->toBeString();
		expect($output)->toContain('REDACTED API key');
		expect($output)->not->toContain($apiKey);
	}
);

it(
	'Redacts tokens from OAuthTokenAuth when using var_dump()',
	function () {
		$accessToken = 'access123';
		$refreshToken = static fn () => 'refresh123';

		// Test without the refresh token first.
		$auth = new OAuthTokenAuth($accessToken);
		ob_start();
		var_dump($auth);
		$output = ob_get_clean();

		expect($output)->toBeString();
		expect($output)->not->toContain($accessToken);
		expect($output)->toContain('REDACTED access token');
		expect($output)->toMatch('/refreshAccessToken["\]]*\s*=>\s*NULL/');

		// Test with the refresh token.
		$auth = new OAuthTokenAuth($accessToken, $refreshToken);
		ob_start();
		var_dump($auth);
		$output = ob_get_clean();

		expect($output)->toBeString();
		expect($output)->not->toContain($accessToken);
		expect($output)->toContain('REDACTED access token');
		expect($output)->toContain('REDACTED refresh token');
	}
);

it(
	'Redacts tokens from OAuthTokenAuth when using print_r()',
	function () {
		$accessToken = 'access123';
		$refreshToken = static fn () => 'refresh123';

		// Test without the refresh token first.
		$auth = new OAuthTokenAuth($accessToken);
		$output = print_r($auth, true);
		expect($output)->toBeString();
		expect($output)->not->toContain($accessToken);
		expect($output)->toContain('REDACTED access token');
		expect($output)->toMatch('/\[refreshAccessToken\] =>\s*\)/');

		// Test with the refresh token.
		$auth = new OAuthTokenAuth($accessToken, $refreshToken);
		$output = print_r($auth, true);
		expect($output)->toBeString();
		expect($output)->not->toContain($accessToken);
		expect($output)->toContain('REDACTED access token');
		expect($output)->toContain('REDACTED refresh token');
	}
);

it(
	'Redacts client credentials from OAuthConfig when using var_dump()',
	function () {
		$clientId = 'client-123';
		$clientSecret = 'secret-456';
		$callbackUrl = 'https://example.com/callback';
		$config = new OAuthConfig(
			clientId: $clientId,
			clientSecret: $clientSecret,
			redirectUri: $callbackUrl,
		);

		ob_start();
		var_dump($config);
		$output = ob_get_clean();

		expect($output)->toBeString();
		expect($output)->not->toContain($clientId);
		expect($output)->not->toContain($clientSecret);
		expect($output)->toContain('REDACTED client ID');
		expect($output)->toContain('REDACTED client secret');
		expect($output)->toContain($callbackUrl);
	}
);

it(
	'Redacts client credentials from OAuthConfig when using print_r()',
	function () {
		$clientId = 'client-123';
		$clientSecret = 'secret-456';
		$callbackUrl = 'https://example.com/callback';
		$config = new OAuthConfig(
			clientId: $clientId,
			clientSecret: $clientSecret,
			redirectUri: $callbackUrl,
		);

		$output = print_r($config, true);

		expect($output)->toBeString();
		expect($output)->not->toContain($clientId);
		expect($output)->not->toContain($clientSecret);
		expect($output)->toContain('REDACTED client ID');
		expect($output)->toContain('REDACTED client secret');
		expect($output)->toContain($callbackUrl);
	}
);

it(
	'Redacts tokens from OAuthTokens when using var_dump()',
	function () {
		$accessToken = 'access-123';
		$refreshToken = 'refresh-456';

		$tokens = new OAuthTokens(accessToken: $accessToken, refreshToken: $refreshToken, expiresIn: 3600);
		ob_start();
		var_dump($tokens);
		$output = ob_get_clean();

		expect($output)->toBeString();
		expect($output)->not->toContain($accessToken);
		expect($output)->not->toContain($refreshToken);
		expect($output)->toContain('REDACTED access token');
		expect($output)->toContain('REDACTED refresh token');
	}
);

it(
	'Redacts tokens from OAuthTokens when using print_r()',
	function () {
		$accessToken = 'access-123';
		$refreshToken = 'refresh-456';

		$tokens = new OAuthTokens(accessToken: $accessToken, refreshToken: $refreshToken, expiresIn: 3600);
		$output = print_r($tokens, true);

		expect($output)->toBeString();
		expect($output)->not->toContain($accessToken);
		expect($output)->not->toContain($refreshToken);
		expect($output)->toContain('REDACTED access token');
		expect($output)->toContain('REDACTED refresh token');
	}
);

it(
	'Throws InvalidStringException when ApiKeyAuth is constructed with an empty string',
	function () {
		expect(fn () => new ApiKeyAuth(''))->toThrow(InvalidStringException::class);
	}
);

it(
	'Throws InvalidStringException when ApiKeyAuth is constructed with a whitespace-only string',
	function () {
		expect(fn () => new ApiKeyAuth('   '))->toThrow(InvalidStringException::class);
		expect(fn () => new ApiKeyAuth("\t"))->toThrow(InvalidStringException::class);
		expect(fn () => new ApiKeyAuth("\n"))->toThrow(InvalidStringException::class);
	}
);

it(
	'Throws InvalidStringException when OAuthTokenAuth is constructed with an empty access token',
	function () {
		expect(fn () => new OAuthTokenAuth(''))->toThrow(InvalidStringException::class);
	}
);

it(
	'Throws InvalidStringException when OAuthTokenAuth is constructed with a whitespace-only access token',
	function () {
		expect(fn () => new OAuthTokenAuth('   '))->toThrow(InvalidStringException::class);
		expect(fn () => new OAuthTokenAuth("\t"))->toThrow(InvalidStringException::class);
		expect(fn () => new OAuthTokenAuth("\n"))->toThrow(InvalidStringException::class);
	}
);

it(
	'Accepts a valid access token with an optional refresh callable in OAuthTokenAuth',
	function () {
		$auth = new OAuthTokenAuth('valid-token');
		expect($auth)->toBeInstanceOf(OAuthTokenAuth::class);

		$authWithRefresh = new OAuthTokenAuth('valid-token', fn () => 'new-token');
		expect($authWithRefresh)->toBeInstanceOf(OAuthTokenAuth::class);
	}
);
