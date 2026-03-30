<?php

declare(strict_types=1);

use JPry\YNAB\Model\Mutation\CreateAccountRequest;
use JPry\YNAB\Model\Mutation\CreatePayeeRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryRequest;
use JPry\YNAB\Model\Mutation\CreateScheduledTransactionRequest;
use JPry\YNAB\Model\Mutation\CreateTransactionsRequest;
use JPry\YNAB\Model\Mutation\ImportTransactionsRequest;
use JPry\YNAB\Model\Mutation\PatchTransactionPayload;
use JPry\YNAB\Model\Mutation\PatchTransactionsRequest;
use JPry\YNAB\Model\Mutation\ScheduledTransactionPayload;
use JPry\YNAB\Model\Mutation\SubTransactionPayload;
use JPry\YNAB\Model\Mutation\TransactionPayload;
use JPry\YNAB\Model\Enum\AccountType;
use JPry\YNAB\Model\Enum\SaveAccountType;
use JPry\YNAB\Model\Enum\ScheduledTransactionFrequency;
use JPry\YNAB\Model\Enum\TransactionClearedStatus;
use JPry\YNAB\Model\Enum\TransactionFlagColor;
use JPry\YNAB\Model\Mutation\UpdateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\UpdateCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdateMonthCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdatePayeeRequest;
use JPry\YNAB\Model\Mutation\UpdateScheduledTransactionRequest;
use JPry\YNAB\Model\Mutation\UpdateTransactionRequest;

// ---------------------------------------------------------------------------
// SubTransactionPayload
// ---------------------------------------------------------------------------

it('SubTransactionPayload toArray() includes all populated fields with snake_case keys', function () {
	$sub = new SubTransactionPayload(
		amount: -5000,
		payeeId: 'P1',
		payeeName: 'Grocery Store',
		categoryId: 'C1',
		memo: 'Weekly shop',
	);

	expect($sub->toArray())->toBe([
		'amount' => -5000,
		'payee_id' => 'P1',
		'payee_name' => 'Grocery Store',
		'category_id' => 'C1',
		'memo' => 'Weekly shop',
	]);
});

it('SubTransactionPayload toArray() excludes null optional fields', function () {
	$sub = new SubTransactionPayload(amount: -3000);

	$result = $sub->toArray();

	expect($result)->toBe(['amount' => -3000]);
	expect($result)->not->toHaveKey('payee_id');
	expect($result)->not->toHaveKey('payee_name');
	expect($result)->not->toHaveKey('category_id');
	expect($result)->not->toHaveKey('memo');
});

// ---------------------------------------------------------------------------
// TransactionPayload
// ---------------------------------------------------------------------------

it('TransactionPayload toArray() includes all populated fields with snake_case keys', function () {
	$tx = new TransactionPayload(
		accountId: 'A1',
		date: '2026-03-01',
		amount: -10000,
		payeeId: 'P1',
		payeeName: 'Coffee Shop',
		categoryId: 'C1',
		memo: 'Morning coffee',
		cleared: TransactionClearedStatus::Cleared,
		approved: true,
		flagColor: TransactionFlagColor::Red,
		subtransactions: [],
		importId: 'IMPORT-001',
	);

	$result = $tx->toArray();

	expect($result)->toMatchArray([
		'account_id' => 'A1',
		'date' => '2026-03-01',
		'amount' => -10000,
		'payee_id' => 'P1',
		'payee_name' => 'Coffee Shop',
		'category_id' => 'C1',
		'memo' => 'Morning coffee',
		'cleared' => 'cleared',
		'approved' => true,
		'flag_color' => 'red',
		'import_id' => 'IMPORT-001',
	]);
	expect($result)->not->toHaveKey('subtransactions');
});

it('TransactionPayload toArray() excludes null optional fields', function () {
	$tx = new TransactionPayload();

	expect($tx->toArray())->toBe([]);
});

it('TransactionPayload toArray() serialises nested subtransactions', function () {
	$sub = new SubTransactionPayload(amount: -2000, memo: 'Split A');
	$tx = new TransactionPayload(
		accountId: 'A1',
		date: '2026-03-01',
		amount: -2000,
		subtransactions: [$sub],
	);

	$result = $tx->toArray();

	expect($result)->toHaveKey('subtransactions');
	expect($result['subtransactions'])->toBe([
		['amount' => -2000, 'memo' => 'Split A'],
	]);
});

// ---------------------------------------------------------------------------
// ScheduledTransactionPayload
// ---------------------------------------------------------------------------

it('ScheduledTransactionPayload toArray() always includes required fields', function () {
	$payload = new ScheduledTransactionPayload(
		accountId: 'A1',
		date: '2026-04-01',
	);

	$result = $payload->toArray();

	expect($result)->toMatchArray([
		'account_id' => 'A1',
		'date' => '2026-04-01',
	]);
	expect($result)->not->toHaveKey('amount');
	expect($result)->not->toHaveKey('payee_id');
	expect($result)->not->toHaveKey('payee_name');
	expect($result)->not->toHaveKey('category_id');
	expect($result)->not->toHaveKey('memo');
	expect($result)->not->toHaveKey('flag_color');
	expect($result)->not->toHaveKey('frequency');
});

it('ScheduledTransactionPayload toArray() includes all populated optional fields', function () {
	$payload = new ScheduledTransactionPayload(
		accountId: 'A1',
		date: '2026-04-01',
		amount: -50000,
		payeeId: 'P2',
		payeeName: 'Landlord',
		categoryId: 'C2',
		memo: 'Monthly rent',
		flagColor: TransactionFlagColor::Blue,
		frequency: ScheduledTransactionFrequency::Monthly,
	);

	expect($payload->toArray())->toBe([
		'account_id' => 'A1',
		'date' => '2026-04-01',
		'amount' => -50000,
		'payee_id' => 'P2',
		'payee_name' => 'Landlord',
		'category_id' => 'C2',
		'memo' => 'Monthly rent',
		'flag_color' => 'blue',
		'frequency' => 'monthly',
	]);
});

// ---------------------------------------------------------------------------
// UpdateCategoryRequest
// ---------------------------------------------------------------------------

it('UpdateCategoryRequest toArray() wraps data under category key', function () {
	$request = new UpdateCategoryRequest(
		id: 'C1',
		name: 'Groceries',
		note: 'Food budget',
		categoryGroupId: 'CG1',
		goalTarget: 30000,
		goalTargetDate: '2026-12-31',
	);

	$result = $request->toArray();

	expect($result)->toHaveKey('category');
	expect($result['category'])->toBe([
		'name' => 'Groceries',
		'note' => 'Food budget',
		'category_group_id' => 'CG1',
		'goal_target' => 30000,
		'goal_target_date' => '2026-12-31',
	]);
});

it('UpdateCategoryRequest toArray() wraps empty array under category key when all optional fields are null', function () {
	$request = new UpdateCategoryRequest(id: 'C1');

	$result = $request->toArray();

	expect($result)->toHaveKey('category');
	expect($result['category'])->toBe([]);
});

it('UpdateCategoryRequest toArray() excludes null optional fields inside category wrapper', function () {
	$request = new UpdateCategoryRequest(id: 'C1', name: 'Transport');

	$inner = $request->toArray()['category'];

	expect($inner)->toHaveKey('name');
	expect($inner)->not->toHaveKey('note');
	expect($inner)->not->toHaveKey('category_group_id');
	expect($inner)->not->toHaveKey('goal_target');
	expect($inner)->not->toHaveKey('goal_target_date');
});

// ---------------------------------------------------------------------------
// UpdatePayeeRequest
// ---------------------------------------------------------------------------

it('UpdatePayeeRequest toArray() wraps data under payee key', function () {
	$request = new UpdatePayeeRequest(id: 'PY1', name: 'New Payee Name');

	$result = $request->toArray();

	expect($result)->toHaveKey('payee');
	expect($result['payee'])->toBe(['name' => 'New Payee Name']);
});

it('UpdatePayeeRequest toArray() wraps empty array under payee key when name is null', function () {
	$request = new UpdatePayeeRequest(id: 'PY1');

	$result = $request->toArray();

	expect($result)->toHaveKey('payee');
	expect($result['payee'])->toBe([]);
});

// ---------------------------------------------------------------------------
// CreateTransactionsRequest
// ---------------------------------------------------------------------------

it('CreateTransactionsRequest::single() toArray() wraps payload under transaction key', function () {
	$payload = new TransactionPayload(accountId: 'A1', date: '2026-03-01', amount: -1000);
	$request = CreateTransactionsRequest::single($payload);

	$result = $request->toArray();

	expect($result)->toHaveKey('transaction');
	expect($result)->not->toHaveKey('transactions');
	expect($result['transaction'])->toMatchArray([
		'account_id' => 'A1',
		'date' => '2026-03-01',
		'amount' => -1000,
	]);
});

it('CreateTransactionsRequest::multiple() toArray() wraps payloads under transactions key', function () {
	$p1 = new TransactionPayload(accountId: 'A1', date: '2026-03-01', amount: -1000);
	$p2 = new TransactionPayload(accountId: 'A1', date: '2026-03-02', amount: -2000);
	$request = CreateTransactionsRequest::multiple([$p1, $p2]);

	$result = $request->toArray();

	expect($result)->toHaveKey('transactions');
	expect($result)->not->toHaveKey('transaction');
	expect($result['transactions'])->toHaveCount(2);
	expect($result['transactions'][0])->toMatchArray(['account_id' => 'A1', 'amount' => -1000]);
	expect($result['transactions'][1])->toMatchArray(['account_id' => 'A1', 'amount' => -2000]);
});

// ---------------------------------------------------------------------------
// UpdateTransactionRequest
// ---------------------------------------------------------------------------

it('UpdateTransactionRequest toArray() wraps payload under transaction key', function () {
	$payload = new TransactionPayload(accountId: 'A1', date: '2026-03-05', amount: -500);
	$request = new UpdateTransactionRequest(id: 'T1', transaction: $payload);

	$result = $request->toArray();

	expect($result)->toHaveKey('transaction');
	expect($result['transaction'])->toMatchArray([
		'account_id' => 'A1',
		'date' => '2026-03-05',
		'amount' => -500,
	]);
});

// ---------------------------------------------------------------------------
// PatchTransactionPayload
// ---------------------------------------------------------------------------

it('PatchTransactionPayload toArray() merges id and import_id into the flat payload', function () {
	$inner = new TransactionPayload(accountId: 'A1', amount: -800);
	$patch = new PatchTransactionPayload(
		transaction: $inner,
		id: 'T1',
		importId: 'IMPORT-T1',
	);

	$result = $patch->toArray();

	expect($result)->toMatchArray([
		'account_id' => 'A1',
		'amount' => -800,
		'id' => 'T1',
		'import_id' => 'IMPORT-T1',
	]);
});

it('PatchTransactionPayload toArray() excludes null id and import_id', function () {
	$inner = new TransactionPayload(amount: -800);
	$patch = new PatchTransactionPayload(transaction: $inner);

	$result = $patch->toArray();

	expect($result)->not->toHaveKey('id');
	expect($result)->not->toHaveKey('import_id');
});

// ---------------------------------------------------------------------------
// PatchTransactionsRequest
// ---------------------------------------------------------------------------

it('PatchTransactionsRequest toArray() wraps patched payloads under transactions key', function () {
	$inner = new TransactionPayload(accountId: 'A1', amount: -100);
	$patch = new PatchTransactionPayload(transaction: $inner, id: 'T1');
	$request = new PatchTransactionsRequest(transactions: [$patch]);

	$result = $request->toArray();

	expect($result)->toHaveKey('transactions');
	expect($result['transactions'])->toHaveCount(1);
	expect($result['transactions'][0])->toMatchArray([
		'account_id' => 'A1',
		'amount' => -100,
		'id' => 'T1',
	]);
});

// ---------------------------------------------------------------------------
// ImportTransactionsRequest
// ---------------------------------------------------------------------------

it('ImportTransactionsRequest toArray() wraps serialised payloads under transactions key', function () {
	$p = new TransactionPayload(accountId: 'A1', date: '2026-03-10', amount: -250, importId: 'IMP-1');
	$request = new ImportTransactionsRequest(transactions: [$p]);

	$result = $request->toArray();

	expect($result)->toHaveKey('transactions');
	expect($result['transactions'])->toHaveCount(1);
	expect($result['transactions'][0])->toMatchArray([
		'account_id' => 'A1',
		'amount' => -250,
		'import_id' => 'IMP-1',
	]);
});

// ---------------------------------------------------------------------------
// CreateScheduledTransactionRequest
// ---------------------------------------------------------------------------

it('CreateScheduledTransactionRequest toArray() wraps payload under scheduled_transaction key', function () {
	$payload = new ScheduledTransactionPayload(
		accountId: 'A1',
		date: '2026-05-01',
		amount: -60000,
		frequency: ScheduledTransactionFrequency::Monthly,
	);
	$request = new CreateScheduledTransactionRequest(scheduledTransaction: $payload);

	$result = $request->toArray();

	expect($result)->toHaveKey('scheduled_transaction');
	expect($result['scheduled_transaction'])->toMatchArray([
		'account_id' => 'A1',
		'date' => '2026-05-01',
		'amount' => -60000,
		'frequency' => 'monthly',
	]);
});

// ---------------------------------------------------------------------------
// UpdateScheduledTransactionRequest
// ---------------------------------------------------------------------------

it('UpdateScheduledTransactionRequest toArray() wraps payload under scheduled_transaction key', function () {
	$payload = new ScheduledTransactionPayload(
		accountId: 'A1',
		date: '2026-06-01',
		memo: 'Updated memo',
	);
	$request = new UpdateScheduledTransactionRequest(id: 'ST1', scheduledTransaction: $payload);

	$result = $request->toArray();

	expect($result)->toHaveKey('scheduled_transaction');
	expect($result['scheduled_transaction'])->toMatchArray([
		'account_id' => 'A1',
		'date' => '2026-06-01',
		'memo' => 'Updated memo',
	]);
});

// ---------------------------------------------------------------------------
// CreateAccountRequest
// ---------------------------------------------------------------------------

it('CreateAccountRequest toArray() wraps fields under account key', function () {
	$request = new CreateAccountRequest(
		name: 'Checking',
		type: SaveAccountType::Checking,
		balance: 100000,
	);

	$result = $request->toArray();

	expect($result)->toHaveKey('account');
	expect($result['account'])->toBe([
		'name' => 'Checking',
		'type' => 'checking',
		'balance' => 100000,
	]);
});

it('CreateAccountRequest accepts legacy AccountType for backward compatibility', function () {
	$request = new CreateAccountRequest(
		name: 'Savings',
		type: AccountType::Savings,
		balance: 50000,
	);

	$result = $request->toArray();

	expect($result['account']['type'])->toBe('savings');
});

it('CreateAccountRequest throws ValueError for AccountType not in SaveAccountType', function () {
	expect(fn () => new CreateAccountRequest(
		name: 'Mortgage',
		type: AccountType::Mortgage,
		balance: 0,
	))->toThrow(ValueError::class);
});

// ---------------------------------------------------------------------------
// CreateCategoryGroupRequest
// ---------------------------------------------------------------------------

it('CreateCategoryGroupRequest toArray() wraps name under category_group key', function () {
	$request = new CreateCategoryGroupRequest(name: 'Savings Goals');

	$result = $request->toArray();

	expect($result)->toHaveKey('category_group');
	expect($result['category_group'])->toBe(['name' => 'Savings Goals']);
});

// ---------------------------------------------------------------------------
// UpdateCategoryGroupRequest
// ---------------------------------------------------------------------------

it('UpdateCategoryGroupRequest toArray() wraps name under category_group key', function () {
	$request = new UpdateCategoryGroupRequest(id: 'CG1', name: 'Renamed Group');

	$result = $request->toArray();

	expect($result)->toHaveKey('category_group');
	expect($result['category_group'])->toBe(['name' => 'Renamed Group']);
});

// ---------------------------------------------------------------------------
// CreateCategoryRequest
// ---------------------------------------------------------------------------

it('CreateCategoryRequest toArray() wraps required fields under category key', function () {
	$request = new CreateCategoryRequest(
		name: 'Groceries',
		categoryGroupId: 'CG1',
	);

	$result = $request->toArray();

	expect($result)->toHaveKey('category');
	expect($result['category'])->toBe([
		'name' => 'Groceries',
		'category_group_id' => 'CG1',
	]);
});

it('CreateCategoryRequest toArray() includes all optional fields when populated', function () {
	$request = new CreateCategoryRequest(
		name: 'Groceries',
		categoryGroupId: 'CG1',
		note: 'Weekly food',
		goalTarget: 30000,
		goalTargetDate: '2026-12-31',
	);

	$result = $request->toArray();

	expect($result['category'])->toBe([
		'name' => 'Groceries',
		'category_group_id' => 'CG1',
		'note' => 'Weekly food',
		'goal_target' => 30000,
		'goal_target_date' => '2026-12-31',
	]);
});

it('CreateCategoryRequest toArray() excludes null optional fields inside category wrapper', function () {
	$request = new CreateCategoryRequest(name: 'Transport', categoryGroupId: 'CG2');

	$inner = $request->toArray()['category'];

	expect($inner)->not->toHaveKey('note');
	expect($inner)->not->toHaveKey('goal_target');
	expect($inner)->not->toHaveKey('goal_target_date');
});

// ---------------------------------------------------------------------------
// UpdateMonthCategoryRequest
// ---------------------------------------------------------------------------

it('UpdateMonthCategoryRequest toArray() wraps budgeted under category key', function () {
	$request = new UpdateMonthCategoryRequest(id: 'C1', budgeted: 50000);

	$result = $request->toArray();

	expect($result)->toHaveKey('category');
	expect($result['category'])->toBe(['budgeted' => 50000]);
});

// ---------------------------------------------------------------------------
// CreatePayeeRequest
// ---------------------------------------------------------------------------

it('CreatePayeeRequest toArray() wraps name under payee key', function () {
	$request = new CreatePayeeRequest(name: 'Grocery Store');

	$result = $request->toArray();

	expect($result)->toHaveKey('payee');
	expect($result['payee'])->toBe(['name' => 'Grocery Store']);
});
