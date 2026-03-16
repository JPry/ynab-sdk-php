<?php

declare(strict_types=1);

use JPry\YNAB\Auth\ApiKeyAuth;
use JPry\YNAB\Auth\OAuthTokenAuth;
use JPry\YNAB\Exception\InvalidStringException;

it(
	'Throws an InvalidStringException when the apiKey/accessToken is empty',
	function () {
		expect(function () {
			new ApiKeyAuth('');
		})->toThrow(InvalidStringException::class);

		expect(function () {
			new OAuthTokenAuth('');
		})->toThrow(InvalidStringException::class);
	}
);

it(
	'Throws an InvalidStringExeption when the the trimmed apiKey/accessToken is empty',
	function () {
		expect(function () {
			new ApiKeyAuth('   ');
		})->toThrow(InvalidStringException::class);

		expect(function () {
			new OAuthTokenAuth('   ');
		})->toThrow(InvalidStringException::class);
	}
);
