<?php

declare(strict_types=1);

use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Exception\YnabApiException;
use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\Model\Mutation\CreateAccountRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\CreatePayeeRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryRequest;
use JPry\YNAB\Model\Mutation\CreateScheduledTransactionRequest;
use JPry\YNAB\Model\Mutation\CreateTransactionsRequest;
use JPry\YNAB\Model\Mutation\ImportTransactionsRequest;
use JPry\YNAB\Model\Mutation\PatchTransactionPayload;
use JPry\YNAB\Model\Mutation\PatchTransactionsRequest;
use JPry\YNAB\Model\Mutation\ScheduledTransactionPayload;
use JPry\YNAB\Model\Mutation\TransactionPayload;
use JPry\YNAB\Model\Mutation\UpdateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\UpdateCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdateMonthCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdatePayeeRequest;
use JPry\YNAB\Model\Mutation\UpdateScheduledTransactionRequest;
use JPry\YNAB\Model\Mutation\UpdateTransactionRequest;
use JPry\YNAB\Model\Enum\AccountType;
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

it('supports months, money movements, scheduled transactions, and payee locations', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"months":[{"month":"2026-03-01","income":100000,"budgeted":60000,"activity":-25000,"to_be_budgeted":40000,"deleted":false}],"server_knowledge":1}}'),
		fn ($request) => new Response(200, [], '{"data":{"month":{"month":"2026-03-01","income":100000,"budgeted":60000,"activity":-25000,"to_be_budgeted":40000,"deleted":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"money_movements":[{"id":"MM1","amount":12000}],"server_knowledge":2}}'),
		fn ($request) => new Response(200, [], '{"data":{"money_movements":[{"id":"MM2","amount":5000}],"server_knowledge":3}}'),
		fn ($request) => new Response(200, [], '{"data":{"money_movement_groups":[{"id":"MMG1","group_created_at":"2026-03-10T12:00:00Z","month":"2026-03-01"}],"server_knowledge":4}}'),
		fn ($request) => new Response(200, [], '{"data":{"money_movement_groups":[{"id":"MMG2","group_created_at":"2026-03-10T13:00:00Z","month":"2026-03-01"}],"server_knowledge":5}}'),
		fn ($request) => new Response(200, [], '{"data":{"scheduled_transactions":[{"id":"ST1","account_id":"A1","date_first":"2026-03-01","date_next":"2026-04-01","frequency":"monthly","amount":-1000,"deleted":false}],"server_knowledge":6}}'),
		fn ($request) => new Response(200, [], '{"data":{"scheduled_transaction":{"id":"ST1","account_id":"A1","date_first":"2026-03-01","date_next":"2026-04-01","frequency":"monthly","amount":-1000,"deleted":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"payee_locations":[{"id":"PL1","payee_id":"PY1","latitude":"41.8781","longitude":"-87.6298","deleted":false}]}}'),
		fn ($request) => new Response(200, [], '{"data":{"payee_location":{"id":"PL1","payee_id":"PY1","latitude":"41.8781","longitude":"-87.6298","deleted":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"payee_locations":[{"id":"PL2","payee_id":"PY1","latitude":"41.8810","longitude":"-87.6200","deleted":false}]}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$months = $client->months('P1');
	$month = $client->month('P1', '2026-03-01');
	$moneyMovements = $client->moneyMovements('P1');
	$moneyMovementsByMonth = $client->moneyMovementsByMonth('P1', '2026-03-01');
	$movementGroups = $client->moneyMovementGroups('P1');
	$movementGroupsByMonth = $client->moneyMovementGroupsByMonth('P1', '2026-03-01');
	$scheduled = $client->scheduledTransactions('P1');
	$scheduledOne = $client->scheduledTransaction('P1', 'ST1');
	$locations = $client->payeeLocations('P1');
	$location = $client->payeeLocation('P1', 'PL1');
	$locationsByPayee = $client->payeeLocationsByPayee('P1', 'PY1');

	expect($months->items)->toHaveCount(1);
	expect($month?->month)->toBe('2026-03-01');
	expect($moneyMovements->items)->toHaveCount(1);
	expect($moneyMovementsByMonth->items)->toHaveCount(1);
	expect($movementGroups->items)->toHaveCount(1);
	expect($movementGroupsByMonth->items)->toHaveCount(1);
	expect($scheduled->items)->toHaveCount(1);
	expect($scheduledOne?->id)->toBe('ST1');
	expect($locations->items)->toHaveCount(1);
	expect($location?->id)->toBe('PL1');
	expect($locationsByPayee->items)->toHaveCount(1);

	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/months');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/months/2026-03-01');
	expect($sender->requests[2]->getUri()->getPath())->toEndWith('/plans/P1/money_movements');
	expect($sender->requests[3]->getUri()->getPath())->toEndWith('/plans/P1/months/2026-03-01/money_movements');
	expect($sender->requests[4]->getUri()->getPath())->toEndWith('/plans/P1/money_movement_groups');
	expect($sender->requests[5]->getUri()->getPath())->toEndWith('/plans/P1/months/2026-03-01/money_movement_groups');
	expect($sender->requests[6]->getUri()->getPath())->toEndWith('/plans/P1/scheduled_transactions');
	expect($sender->requests[7]->getUri()->getPath())->toEndWith('/plans/P1/scheduled_transactions/ST1');
	expect($sender->requests[8]->getUri()->getPath())->toEndWith('/plans/P1/payee_locations');
	expect($sender->requests[9]->getUri()->getPath())->toEndWith('/plans/P1/payee_locations/PL1');
	expect($sender->requests[10]->getUri()->getPath())->toEndWith('/plans/P1/payees/PY1/payee_locations');
});

it('supports additional plan-scoped read endpoints from the openapi audit', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"plan":{"id":"P1","name":"Main"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"account":{"id":"A1","name":"Checking","type":"checking","closed":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"payee":{"id":"PY1","name":"Store","deleted":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"category_groups":[{"id":"CG1","name":"Essentials","hidden":false,"deleted":false}],"server_knowledge":1}}'),
		fn ($request) => new Response(200, [], '{"data":{"category_group":{"id":"CG1","name":"Essentials","hidden":false,"deleted":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"transaction":{"id":"T1","account_id":"A1","amount":-1000,"is_pending":false},"server_knowledge":1}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T2","account_id":"A1","amount":-1000,"is_pending":false}],"server_knowledge":2}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T3","account_id":"A1","amount":-1000,"is_pending":false}],"server_knowledge":3}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T4","account_id":"A1","amount":-1000,"is_pending":false}],"server_knowledge":4}}'),
		fn ($request) => new Response(200, [], '{"data":{"transactions":[{"id":"T5","account_id":"A1","amount":-1000,"is_pending":false}],"server_knowledge":5}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$plan = $client->plan('P1');
	$account = $client->account('P1', 'A1');
	$payee = $client->payee('P1', 'PY1');
	$groups = $client->categoryGroups('P1');
	$group = $client->categoryGroup('P1', 'CG1');
	$transaction = $client->transaction('P1', 'T1');
	$txByAccount = $client->transactionsByAccount('P1', 'A1');
	$txByCategory = $client->transactionsByCategory('P1', 'C1');
	$txByPayee = $client->transactionsByPayee('P1', 'PY1');
	$txByMonth = $client->transactionsByMonth('P1', '2026-03-01');

	expect($plan?->id)->toBe('P1');
	expect($account?->id)->toBe('A1');
	expect($payee?->id)->toBe('PY1');
	expect($groups->items)->toHaveCount(1);
	expect($group?->id)->toBe('CG1');
	expect($transaction?->id)->toBe('T1');
	expect($txByAccount->items)->toHaveCount(1);
	expect($txByCategory->items)->toHaveCount(1);
	expect($txByPayee->items)->toHaveCount(1);
	expect($txByMonth->items)->toHaveCount(1);

	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/accounts/A1');
	expect($sender->requests[2]->getUri()->getPath())->toEndWith('/plans/P1/payees/PY1');
	expect($sender->requests[3]->getUri()->getPath())->toEndWith('/plans/P1/category_groups');
	expect($sender->requests[4]->getUri()->getPath())->toEndWith('/plans/P1/category_groups/CG1');
	expect($sender->requests[5]->getUri()->getPath())->toEndWith('/plans/P1/transactions/T1');
	expect($sender->requests[6]->getUri()->getPath())->toEndWith('/plans/P1/accounts/A1/transactions');
	expect($sender->requests[7]->getUri()->getPath())->toEndWith('/plans/P1/categories/C1/transactions');
	expect($sender->requests[8]->getUri()->getPath())->toEndWith('/plans/P1/payees/PY1/transactions');
	expect($sender->requests[9]->getUri()->getPath())->toEndWith('/plans/P1/months/2026-03-01/transactions');
});

it('retrieves category details for current and explicit month contexts', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"category":{"id":"C1","category_group_id":"CG1","category_group_name":"Essentials","name":"Groceries","budgeted":25000,"activity":-12000,"balance":13000,"hidden":false,"deleted":false}}}'),
		fn ($request) => new Response(200, [], '{"data":{"category":{"id":"C1","category_group_id":"CG1","category_group_name":"Essentials","name":"Groceries","budgeted":26000,"activity":-10000,"balance":16000,"hidden":false,"deleted":false}}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$category = $client->category('P1', 'C1');
	$monthCategory = $client->monthCategory('P1', '2026-03-01', 'C1');

	expect($category?->id)->toBe('C1');
	expect($category?->budgeted)->toBe(25000);
	expect($monthCategory?->budgeted)->toBe(26000);

	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/categories/C1');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/months/2026-03-01/categories/C1');
});

it('supports plan-scoped write endpoints from openapi coverage audit', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"transaction_ids":["T1"]}}'),
		fn ($request) => new Response(200, [], '{"data":{"transaction_ids":["T2"]}}'),
		fn ($request) => new Response(200, [], '{"data":{"transaction":{"id":"T1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"transaction":{"id":"T1"}}}'),
		fn ($request) => new Response(201, [], '{"data":{"scheduled_transaction":{"id":"ST1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"scheduled_transaction":{"id":"ST1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"scheduled_transaction":{"id":"ST1"}}}'),
		fn ($request) => new Response(201, [], '{"data":{"account":{"id":"A1"}}}'),
		fn ($request) => new Response(201, [], '{"data":{"category":{"id":"C1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"category":{"id":"C1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"category":{"id":"C1"}}}'),
		fn ($request) => new Response(201, [], '{"data":{"category_group":{"id":"CG1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"category_group":{"id":"CG1"}}}'),
		fn ($request) => new Response(201, [], '{"data":{"payee":{"id":"PY2"},"server_knowledge":1}}'),
		fn ($request) => new Response(200, [], '{"data":{"payee":{"id":"PY1"}}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$client->createTransactions('P1', CreateTransactionsRequest::single(new TransactionPayload(accountId: 'A1', amount: -1000)));
	$client->importTransactions('P1', new ImportTransactionsRequest([
		new TransactionPayload(accountId: 'A1', amount: -1000),
	]));
	$client->updateTransaction('P1', new UpdateTransactionRequest('T1', new TransactionPayload(memo: 'Updated')));
	$client->deleteTransaction('P1', new UpdateTransactionRequest('T1', new TransactionPayload()));
	$client->createScheduledTransaction('P1', new CreateScheduledTransactionRequest(new ScheduledTransactionPayload(accountId: 'A1', date: '2026-04-01')));
	$client->updateScheduledTransaction(
		'P1',
		new UpdateScheduledTransactionRequest('ST1', new ScheduledTransactionPayload(accountId: 'A1', date: '2026-04-01', memo: 'Changed')),
	);
	$client->deleteScheduledTransaction('P1', new UpdateScheduledTransactionRequest('ST1', new ScheduledTransactionPayload(accountId: 'A1', date: '2026-04-01')));
	$client->createAccount('P1', new CreateAccountRequest(name: 'New Account', type: AccountType::Checking, balance: 0));
	$client->createCategory('P1', new CreateCategoryRequest(name: 'New Category', categoryGroupId: 'CG1'));
	$client->updateCategory('P1', new UpdateCategoryRequest(id: 'C1', name: 'Renamed'));
	$client->updateMonthCategory('P1', '2026-03-01', new UpdateMonthCategoryRequest(id: 'C1', budgeted: 1234));
	$client->createCategoryGroup('P1', new CreateCategoryGroupRequest(name: 'New Group'));
	$client->updateCategoryGroup('P1', new UpdateCategoryGroupRequest(id: 'CG1', name: 'Renamed Group'));
	$client->createPayee('P1', new CreatePayeeRequest(name: 'New Payee'));
	$client->updatePayee('P1', new UpdatePayeeRequest(id: 'PY1', name: 'Renamed Payee'));

	expect($sender->requests[0]->getMethod())->toBe('POST');
	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/transactions');
	expect((string) $sender->requests[0]->getBody())->toContain('"transaction"');

	expect($sender->requests[1]->getMethod())->toBe('POST');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/transactions/import');

	expect($sender->requests[2]->getMethod())->toBe('PUT');
	expect($sender->requests[2]->getUri()->getPath())->toEndWith('/plans/P1/transactions/T1');

	expect($sender->requests[3]->getMethod())->toBe('DELETE');
	expect($sender->requests[3]->getUri()->getPath())->toEndWith('/plans/P1/transactions/T1');

	expect($sender->requests[4]->getMethod())->toBe('POST');
	expect($sender->requests[4]->getUri()->getPath())->toEndWith('/plans/P1/scheduled_transactions');

	expect($sender->requests[5]->getMethod())->toBe('PUT');
	expect($sender->requests[5]->getUri()->getPath())->toEndWith('/plans/P1/scheduled_transactions/ST1');

	expect($sender->requests[6]->getMethod())->toBe('DELETE');
	expect($sender->requests[6]->getUri()->getPath())->toEndWith('/plans/P1/scheduled_transactions/ST1');

	expect($sender->requests[7]->getMethod())->toBe('POST');
	expect($sender->requests[7]->getUri()->getPath())->toEndWith('/plans/P1/accounts');

	expect($sender->requests[8]->getMethod())->toBe('POST');
	expect($sender->requests[8]->getUri()->getPath())->toEndWith('/plans/P1/categories');

	expect($sender->requests[9]->getMethod())->toBe('PATCH');
	expect($sender->requests[9]->getUri()->getPath())->toEndWith('/plans/P1/categories/C1');

	expect($sender->requests[10]->getMethod())->toBe('PATCH');
	expect($sender->requests[10]->getUri()->getPath())->toEndWith('/plans/P1/months/2026-03-01/categories/C1');

	expect($sender->requests[11]->getMethod())->toBe('POST');
	expect($sender->requests[11]->getUri()->getPath())->toEndWith('/plans/P1/category_groups');

	expect($sender->requests[12]->getMethod())->toBe('PATCH');
	expect($sender->requests[12]->getUri()->getPath())->toEndWith('/plans/P1/category_groups/CG1');

	expect($sender->requests[13]->getMethod())->toBe('POST');
	expect($sender->requests[13]->getUri()->getPath())->toEndWith('/plans/P1/payees');

	expect($sender->requests[14]->getMethod())->toBe('PATCH');
	expect($sender->requests[14]->getUri()->getPath())->toEndWith('/plans/P1/payees/PY1');
});

it('accepts typed patch transaction request models', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"transaction_ids":["T1"],"server_knowledge":1}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$client->patchTransactions('P1', new PatchTransactionsRequest([
		new PatchTransactionPayload(
			transaction: new TransactionPayload(memo: 'Patched from model'),
			id: 'T1',
		),
	]));

	expect($sender->requests[0]->getMethod())->toBe('PATCH');
	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/transactions');
	expect((string) $sender->requests[0]->getBody())->toContain('"id":"T1"');
	expect((string) $sender->requests[0]->getBody())->toContain('"memo":"Patched from model"');
});

it('keeps legacy mutating signatures compatible with v1.0.0', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"transaction_ids":["T1"],"server_knowledge":1}}'),
		fn ($request) => new Response(200, [], '{"data":{"transaction":{"id":"T1"}}}'),
		fn ($request) => new Response(200, [], '{"data":{"transaction":{"id":"T1"}}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$client->patchTransactions('P1', ['transactions' => [['id' => 'T1', 'memo' => 'Legacy patch']]]);
	$client->updateTransaction('P1', 'T1', ['transaction' => ['memo' => 'Legacy update']]);
	$client->deleteTransaction('P1', 'T1');

	expect($sender->requests[0]->getMethod())->toBe('PATCH');
	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/transactions');
	expect((string) $sender->requests[0]->getBody())->toContain('"transactions"');

	expect($sender->requests[1]->getMethod())->toBe('PUT');
	expect($sender->requests[1]->getUri()->getPath())->toEndWith('/plans/P1/transactions/T1');
	expect((string) $sender->requests[1]->getBody())->toContain('"transaction"');

	expect($sender->requests[2]->getMethod())->toBe('DELETE');
	expect($sender->requests[2]->getUri()->getPath())->toEndWith('/plans/P1/transactions/T1');
});

// TEST-06: accounts() and payees() collection endpoint tests

it('retrieves accounts collection with all fields populated', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"accounts":[{"id":"A1","name":"Checking","type":"checking","on_budget":true,"closed":false,"balance":100000,"cleared_balance":90000,"uncleared_balance":10000,"deleted":false}],"server_knowledge":5}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$accounts = $client->accounts('P1');

	expect($accounts->items)->toHaveCount(1);
	expect($accounts->serverKnowledge)->toBe(5);

	$account = $accounts->items[0];
	expect($account?->id)->toBe('A1');
	expect($account?->name)->toBe('Checking');
	expect($account?->type)->toBe('checking');
	expect($account?->onBudget)->toBeTrue();
	expect($account?->closed)->toBeFalse();
	expect($account?->balance)->toBe(100000);
	expect($account?->clearedBalance)->toBe(90000);
	expect($account?->unclearedBalance)->toBe(10000);
	expect($account?->deleted)->toBeFalse();

	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/accounts');
});

it('retrieves payees collection with all fields populated', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], '{"data":{"payees":[{"id":"PY1","name":"Grocery Store","transfer_account_id":null,"deleted":false}],"server_knowledge":3}}'),
	]);

	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$payees = $client->payees('P1');

	expect($payees->items)->toHaveCount(1);
	expect($payees->serverKnowledge)->toBe(3);

	$payee = $payees->items[0];
	expect($payee?->id)->toBe('PY1');
	expect($payee?->name)->toBe('Grocery Store');
	expect($payee?->transferAccountId)->toBeNull();
	expect($payee?->deleted)->toBeFalse();

	expect($sender->requests[0]->getUri()->getPath())->toEndWith('/plans/P1/payees');
});

// TEST-07: updateTransaction() error path tests

it('throws YnabException when a RequestModel is passed alongside a non-null payload', function () {
	$sender = new ArrayRequestSender([]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	expect(fn () => $client->updateTransaction(
		'P1',
		new UpdateTransactionRequest('T1', new TransactionPayload(memo: 'Updated')),
		['transaction' => ['memo' => 'Should not be here']],
	))->toThrow(YnabException::class);
});

it('throws YnabException when a string ID is passed without a payload', function () {
	$sender = new ArrayRequestSender([]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	expect(fn () => $client->updateTransaction('P1', 'T1'))->toThrow(YnabException::class);
});
