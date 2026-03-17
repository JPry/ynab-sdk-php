<?php

declare(strict_types=1);

use JPry\YNAB\Model\Account;
use JPry\YNAB\Model\CategoryDetail;
use JPry\YNAB\Model\Payee;
use JPry\YNAB\OAuth\OAuthTokens;
use JPry\YNAB\Model\CategoryGroup;
use JPry\YNAB\Model\MoneyMovement;
use JPry\YNAB\Model\MoneyMovementGroup;
use JPry\YNAB\Model\Month;
use JPry\YNAB\Model\PayeeLocation;
use JPry\YNAB\Model\Plan;
use JPry\YNAB\Model\PlanSettings;
use JPry\YNAB\Model\ScheduledTransaction;
use JPry\YNAB\Model\Transaction;
use JPry\YNAB\Model\User;

it('maps plan rows into typed objects', function () {
	$plan = Plan::fromArray(['id' => 'P1', 'name' => 'Main']);

	expect($plan)->not->toBeNull();
	expect($plan?->id)->toBe('P1');
	expect($plan?->name)->toBe('Main');
});

it('maps transaction rows and keeps raw payload', function () {
	$row = [
		'id' => 'T1',
		'account_id' => 'A1',
		'amount' => -1234,
		'is_pending' => false,
		'memo' => 'Note',
	];

	$tx = Transaction::fromArray($row);

	expect($tx)->not->toBeNull();
	expect($tx?->id)->toBe('T1');
	expect($tx?->amount)->toBe(-1234);
	expect($tx?->raw)->toBe($row);
});

it('maps user rows into typed objects', function () {
	$user = User::fromArray(['id' => 'U1']);

	expect($user)->not->toBeNull();
	expect($user?->id)->toBe('U1');
});

it('maps plan settings into typed objects', function () {
	$row = [
		'date_format' => [
			'format' => 'YYYY-MM-DD',
		],
		'currency_format' => [
			'iso_code' => 'USD',
			'currency_symbol' => '$',
			'decimal_digits' => 2,
			'decimal_separator' => '.',
			'group_separator' => ',',
			'symbol_first' => true,
			'display_symbol' => true,
			'example_format' => '$12,345.67',
		],
	];

	$settings = PlanSettings::fromArray($row);

	expect($settings->dateFormat)->toBe('YYYY-MM-DD');
	expect($settings->currencyIsoCode)->toBe('USD');
	expect($settings->currencySymbol)->toBe('$');
	expect($settings->currencyDecimalDigits)->toBe(2);
	expect($settings->currencyDecimalSeparator)->toBe('.');
	expect($settings->currencyGroupSeparator)->toBe(',');
	expect($settings->currencySymbolFirst)->toBeTrue();
	expect($settings->currencyDisplaySymbol)->toBeTrue();
	expect($settings->currencyExampleFormat)->toBe('$12,345.67');
});

it('maps month rows into typed objects', function () {
	$month = Month::fromArray([
		'month' => '2026-03-01',
		'income' => 100000,
		'budgeted' => 60000,
		'activity' => -25000,
		'to_be_budgeted' => 40000,
		'age_of_money' => 32,
		'deleted' => false,
	]);

	expect($month)->not->toBeNull();
	expect($month?->month)->toBe('2026-03-01');
	expect($month?->toBeBudgeted)->toBe(40000);
});

it('maps money movement rows into typed objects', function () {
	$movement = MoneyMovement::fromArray([
		'id' => 'MM1',
		'month' => '2026-03-01',
		'moved_at' => '2026-03-10T12:00:00Z',
		'amount' => 12000,
		'from_category_id' => 'C1',
		'to_category_id' => 'C2',
	]);

	expect($movement)->not->toBeNull();
	expect($movement?->id)->toBe('MM1');
	expect($movement?->amount)->toBe(12000);
	expect($movement?->fromCategoryId)->toBe('C1');
});

it('maps money movement group rows into typed objects', function () {
	$group = MoneyMovementGroup::fromArray([
		'id' => 'MMG1',
		'group_created_at' => '2026-03-10T12:00:00Z',
		'month' => '2026-03-01',
	]);

	expect($group)->not->toBeNull();
	expect($group?->id)->toBe('MMG1');
	expect($group?->groupCreatedAt)->toBe('2026-03-10T12:00:00Z');
});

it('maps payee location rows into typed objects', function () {
	$location = PayeeLocation::fromArray([
		'id' => 'PL1',
		'payee_id' => 'P1',
		'latitude' => '41.8781',
		'longitude' => '-87.6298',
		'deleted' => false,
	]);

	expect($location)->not->toBeNull();
	expect($location?->id)->toBe('PL1');
	expect($location?->payeeId)->toBe('P1');
});

it('maps scheduled transaction rows into typed objects', function () {
	$scheduled = ScheduledTransaction::fromArray([
		'id' => 'ST1',
		'account_id' => 'A1',
		'date_first' => '2026-03-01',
		'date_next' => '2026-04-01',
		'frequency' => 'monthly',
		'amount' => -1000,
		'deleted' => false,
	]);

	expect($scheduled)->not->toBeNull();
	expect($scheduled?->id)->toBe('ST1');
	expect($scheduled?->frequency)->toBe('monthly');
	expect($scheduled?->amount)->toBe(-1000);
});

it('maps category group rows into typed objects', function () {
	$group = CategoryGroup::fromArray([
		'id' => 'CG1',
		'name' => 'Essentials',
		'hidden' => false,
		'deleted' => false,
	]);

	expect($group)->not->toBeNull();
	expect($group?->id)->toBe('CG1');
	expect($group?->name)->toBe('Essentials');
});

it('maps category detail rows into typed objects', function () {
	$category = CategoryDetail::fromArray([
		'id' => 'C1',
		'category_group_id' => 'CG1',
		'category_group_name' => 'Essentials',
		'name' => 'Groceries',
		'note' => 'Food',
		'budgeted' => 25000,
		'activity' => -12000,
		'balance' => 13000,
		'hidden' => false,
		'deleted' => false,
	]);

	expect($category)->not->toBeNull();
	expect($category?->id)->toBe('C1');
	expect($category?->categoryGroupId)->toBe('CG1');
	expect($category?->budgeted)->toBe(25000);
});

it('maps account rows into typed objects', function () {
	$account = Account::fromArray([
		'id' => 'A1',
		'name' => 'Checking',
		'type' => 'checking',
		'closed' => false,
	]);

	expect($account)->not->toBeNull();
	expect($account?->id)->toBe('A1');
	expect($account?->name)->toBe('Checking');
	expect($account?->type)->toBe('checking');
	expect($account?->closed)->toBeFalse();
});

it('returns null from Account::fromArray when id is missing', function () {
	$account = Account::fromArray([
		'name' => 'Checking',
		'type' => 'checking',
		'closed' => false,
	]);

	expect($account)->toBeNull();
});

it('maps payee rows into typed objects', function () {
	$payee = Payee::fromArray([
		'id' => 'PY1',
		'name' => 'Grocery Store',
		'transfer_account_id' => 'A2',
		'deleted' => false,
	]);

	expect($payee)->not->toBeNull();
	expect($payee?->id)->toBe('PY1');
	expect($payee?->name)->toBe('Grocery Store');
	expect($payee?->transferAccountId)->toBe('A2');
	expect($payee?->deleted)->toBeFalse();
});

it('maps payee rows with empty string transfer_account_id to null', function () {
	$payee = Payee::fromArray([
		'id' => 'PY2',
		'name' => 'Coffee Shop',
		'transfer_account_id' => '',
		'deleted' => false,
	]);

	expect($payee)->not->toBeNull();
	expect($payee?->transferAccountId)->toBeNull();
});

it('returns null from Payee::fromArray when id is missing', function () {
	$payee = Payee::fromArray([
		'name' => 'Some Store',
		'deleted' => false,
	]);

	expect($payee)->toBeNull();
});

it('maps OAuthTokens::fromArray to typed object', function () {
	$tokens = OAuthTokens::fromArray([
		'access_token' => 'access-abc',
		'refresh_token' => 'refresh-xyz',
		'expires_in' => 7200,
		'token_type' => 'Bearer',
	]);

	expect($tokens)->not->toBeNull();
	expect($tokens?->accessToken)->toBe('access-abc');
	expect($tokens?->refreshToken)->toBe('refresh-xyz');
	expect($tokens?->expiresIn)->toBe(7200);
	expect($tokens?->tokenType)->toBe('Bearer');
});

it('returns null from OAuthTokens::fromArray when access_token is missing', function () {
	$tokens = OAuthTokens::fromArray([
		'refresh_token' => 'refresh-xyz',
		'expires_in' => 7200,
		'token_type' => 'Bearer',
	]);

	expect($tokens)->toBeNull();
});
