<?php

declare(strict_types=1);

use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Exception\YnabApiException;
use JPry\YNAB\Tests\Fakes\ArrayRequestSender;
use GuzzleHttp\Psr7\Response;

it('supports api key auth and paginates transactions with next_page', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"plans":[{"id":"P1","name":"Main"}]}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T1","account_id":"A1","amount":-1000,"is_pending":false}],"next_page":2,"server_knowledge":10}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T2","account_id":"A1","amount":-2000,"is_pending":false}],"server_knowledge":11}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$plans = $client->plans();
	$transactions = $client->transactions('P1');

	expect($plans->items)->toHaveCount(1);
	expect($transactions->items)->toHaveCount(2);
	expect($transactions->serverKnowledge)->toBe(11);

	expect($sender->requests[0]->getHeaderLine('Authorization'))->toBe('Bearer api-key-123');
	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/transactions');
	expect($sender->requests[2]->getUri()->getPath())->toEndWith('/plans/P1/transactions');
	parse_str($sender->requests[2]->getUri()->getQuery(), $query);
	expect($query['page'] ?? null)->toBe('2');
});

it('refreshes oauth token on first unauthorized response', function () {
	$calls = 0;
	$sender = new ArrayRequestSender([
		function ($request) use (&$calls): Response {
			$calls++;
			return new Response(401, [], '{"error":{"id":"401","name":"unauthorized"}}');
		},
		function ($request) use (&$calls): Response {
			$calls++;
			return new Response(200, [], '{"data":{"plans":[{"id":"P1","name":"Main"}]}}');
		},
	]);

	$rotations = 0;
	$client = YnabClient::withOAuthToken(
		'expired-token',
		refreshAccessToken: function () use (&$rotations): string {
			$rotations++;
			return 'fresh-token';
		},
		requestSender: $sender,
	);

	$result = $client->plans();

	expect($result->items)->toHaveCount(1);
	expect($calls)->toBe(2);
	expect($rotations)->toBe(1);
});

it('throws structured api exception for ynab error payloads', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(403, [], '{"error":{"id":"403.1","name":"subscription_lapsed","detail":"Subscription expired."}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	try {
		$client->plans();
		throw new RuntimeException('Expected YnabApiException was not thrown.');
	} catch (YnabApiException $e) {
		expect($e->statusCode)->toBe(403);
		expect($e->errorId)->toBe('403.1');
		expect($e->errorName)->toBe('subscription_lapsed');
		expect($e->identifier())->toBe('403.1 subscription_lapsed');
	}
});

it('accepts legacy budget response keys on plan methods', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"budgets":[{"id":"B1","name":"Main"}]}}'),
		fn ($request) => new Response(200, [], '{"data":{"budget":{"id":"B1","name":"Main"}}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$plans = $client->plans();
	$defaultPlan = $client->defaultPlan();

	expect($plans->items)->toHaveCount(1);
	expect($plans->items[0]?->id)->toBe('B1');
	expect($defaultPlan?->id)->toBe('B1');
});

it('keeps budget-named methods and emits deprecation warnings', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"plans":[{"id":"P1","name":"Main"}]}}'),
		fn ($request) => new Response(200, [], '{"data":{"plan":{"id":"P1","name":"Main"}}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$warnings = [];
	$handler = static function (int $errno, string $errstr) use (&$warnings): bool {
		if ($errno === E_USER_DEPRECATED) {
			$warnings[] = $errstr;
			return true;
		}

		return false;
	};

	set_error_handler($handler);
	try {
		$budgets = $client->budgets();
		$defaultBudget = $client->defaultBudget();
	} finally {
		restore_error_handler();
	}

	$joinedWarnings = implode("\n", $warnings);

	expect($budgets->items)->toHaveCount(1);
	expect($defaultBudget?->id)->toBe('P1');
	expect($joinedWarnings)->toContain('budgets() is deprecated');
	expect($joinedWarnings)->toContain('defaultBudget() is deprecated');
});

it('retrieves user and plan settings', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"user":{"id":"U1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"settings":{"date_format":{"format":"YYYY-MM-DD"},"currency_format":{"iso_code":"USD","example_format":"$12,345.67","decimal_digits":2,"decimal_separator":".","symbol_first":true,"group_separator":",","currency_symbol":"$","display_symbol":true}}}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$user = $client->user();
	$settings = $client->planSettings('P1');

	expect($user?->id)->toBe('U1');
	expect($settings?->dateFormat)->toBe('YYYY-MM-DD');
	expect($settings?->currencyIsoCode)->toBe('USD');
	expect($settings?->currencySymbol)->toBe('$');

	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/user');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/settings');
});
