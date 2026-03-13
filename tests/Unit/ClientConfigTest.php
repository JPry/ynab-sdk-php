<?php

declare(strict_types=1);

use JPry\YNAB\Config\ClientConfig;

it('accepts secure https base urls by default', function () {
	$config = new ClientConfig(baseUrl: 'https://api.ynab.com/v1');

	expect($config->baseUrl)->toBe('https://api.ynab.com/v1');
	expect($config->allowInsecure)->toBeFalse();
});

it('rejects insecure http base urls by default', function () {
	expect(fn () => new ClientConfig(baseUrl: 'http://evil.com'))
		->toThrow(InvalidArgumentException::class, 'must start with https://');
});

it('allows insecure http base urls when explicitly enabled', function () {
	$config = new ClientConfig(baseUrl: 'http://localhost', allowInsecure: true);

	expect($config->baseUrl)->toBe('http://localhost');
	expect($config->allowInsecure)->toBeTrue();
});
