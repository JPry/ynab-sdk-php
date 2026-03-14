<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\OAuth\OAuthClient;
use JPry\YNAB\OAuth\OAuthConfig;
use JPry\YNAB\OAuth\OAuthTokens;
use JPry\YNAB\Tests\Fakes\ArrayRequestSender;

function makeOAuthConfig(): OAuthConfig
{
	return new OAuthConfig(
		clientId: 'my-client-id',
		clientSecret: 'my-client-secret',
		redirectUri: 'https://example.com/callback',
		authorizeUrl: 'https://app.ynab.com/oauth/authorize',
		tokenUrl: 'https://app.ynab.com/oauth/token',
	);
}

it('authorizationUrl contains client_id, redirect_uri, response_type=code, and state', function () {
	$config = makeOAuthConfig();
	$sender = new ArrayRequestSender([]);
	$client = new OAuthClient($config, $sender);

	$url = $client->authorizationUrl('my-state-value');

	parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $params);

	expect($url)->toContain('https://app.ynab.com/oauth/authorize');
	expect($params['client_id'])->toBe('my-client-id');
	expect($params['redirect_uri'])->toBe('https://example.com/callback');
	expect($params['response_type'])->toBe('code');
	expect($params['state'])->toBe('my-state-value');
});

it('exchangeCodeForTokens posts correct body params and returns OAuthTokens', function () {
	$config = makeOAuthConfig();
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode([
			'access_token' => 'access-abc',
			'refresh_token' => 'refresh-xyz',
			'expires_in' => 7200,
			'token_type' => 'Bearer',
		])),
	]);
	$client = new OAuthClient($config, $sender);

	$tokens = $client->exchangeCodeForTokens('auth-code-123');

	expect($tokens)->toBeInstanceOf(OAuthTokens::class);
	expect($tokens->accessToken)->toBe('access-abc');
	expect($tokens->refreshToken)->toBe('refresh-xyz');
	expect($tokens->expiresIn)->toBe(7200);
	expect($tokens->tokenType)->toBe('Bearer');

	$request = $sender->requests[0];
	expect($request->getMethod())->toBe('POST');
	expect((string) $request->getUri())->toBe('https://app.ynab.com/oauth/token');

	parse_str((string) $request->getBody(), $body);
	expect($body['grant_type'])->toBe('authorization_code');
	expect($body['code'])->toBe('auth-code-123');
	expect($body['redirect_uri'])->toBe('https://example.com/callback');
	expect($body['client_id'])->toBe('my-client-id');
	expect($body['client_secret'])->toBe('my-client-secret');
});

it('refreshAccessToken posts grant_type=refresh_token and refresh_token and returns OAuthTokens', function () {
	$config = makeOAuthConfig();
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode([
			'access_token' => 'new-access-token',
			'refresh_token' => 'new-refresh-token',
			'expires_in' => 3600,
			'token_type' => 'Bearer',
		])),
	]);
	$client = new OAuthClient($config, $sender);

	$tokens = $client->refreshAccessToken('old-refresh-token');

	expect($tokens)->toBeInstanceOf(OAuthTokens::class);
	expect($tokens->accessToken)->toBe('new-access-token');
	expect($tokens->refreshToken)->toBe('new-refresh-token');

	$request = $sender->requests[0];
	expect($request->getMethod())->toBe('POST');

	parse_str((string) $request->getBody(), $body);
	expect($body['grant_type'])->toBe('refresh_token');
	expect($body['refresh_token'])->toBe('old-refresh-token');
});

it('exchangeCodeForTokens throws YnabException when access_token is missing', function () {
	$config = makeOAuthConfig();
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['error' => 'invalid_grant'])),
	]);
	$client = new OAuthClient($config, $sender);

	expect(fn () => $client->exchangeCodeForTokens('bad-code'))
		->toThrow(YnabException::class, 'Could not parse OAuth token response.');
});

it('refreshAccessToken throws YnabException when access_token is missing', function () {
	$config = makeOAuthConfig();
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(400, [], json_encode(['error' => 'invalid_token'])),
	]);
	$client = new OAuthClient($config, $sender);

	expect(fn () => $client->refreshAccessToken('expired-refresh-token'))
		->toThrow(YnabException::class, 'Could not parse OAuth token response.');
});
