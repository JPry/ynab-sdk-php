<?php

declare(strict_types=1);

use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Exception\YnabApiException;
use JPry\YNAB\Tests\Fakes\ArrayRequestSender;
use GuzzleHttp\Psr7\Response;

it('supports api key auth and paginates transactions with next_page', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"budgets":[{"id":"B1","name":"Main"}]}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T1","account_id":"A1","amount":-1000,"is_pending":false}],"next_page":2,"server_knowledge":10}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T2","account_id":"A1","amount":-2000,"is_pending":false}],"server_knowledge":11}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$budgets = $client->budgets();
	$transactions = $client->transactions('B1');

	expect($budgets->items)->toHaveCount(1);
	expect($transactions->items)->toHaveCount(2);
	expect($transactions->serverKnowledge)->toBe(11);

	expect($sender->requests[0]->getHeaderLine('Authorization'))->toBe('Bearer api-key-123');
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
			return new Response(200, [], '{"data":{"budgets":[{"id":"B1","name":"Main"}]}}');
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

	$result = $client->budgets();

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
		$client->budgets();
		throw new RuntimeException('Expected YnabApiException was not thrown.');
	} catch (YnabApiException $e) {
		expect($e->statusCode)->toBe(403);
		expect($e->errorId)->toBe('403.1');
		expect($e->errorName)->toBe('subscription_lapsed');
		expect($e->identifier())->toBe('403.1 subscription_lapsed');
	}
});
