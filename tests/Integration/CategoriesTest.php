<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Config\ClientConfig;
use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\Model\Category;
use JPry\YNAB\Tests\Fakes\ArrayRequestSender;

it('flattens grouped categories into a ResourceCollection', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['data' => ['category_groups' => [
			['id' => 'CG1', 'name' => 'Essentials', 'categories' => [
				['id' => 'C1', 'name' => 'Groceries', 'hidden' => false, 'deleted' => false],
				['id' => 'C2', 'name' => 'Utilities', 'hidden' => false, 'deleted' => false],
			]],
			['id' => 'CG2', 'name' => 'Savings', 'categories' => [
				['id' => 'C3', 'name' => 'Emergency Fund', 'hidden' => false, 'deleted' => false],
			]],
		], 'server_knowledge' => 5]])),
	]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$result = $client->categories('P1');

	expect($result->items)->toHaveCount(3);
	expect($result->serverKnowledge)->toBe(5);

	/** @var Category $first */
	$first = $result->items[0];
	expect($first->id)->toBe('C1');
	expect($first->name)->toBe('Groceries');
	expect($first->groupId)->toBe('CG1');
	expect($first->groupName)->toBe('Essentials');
	expect($first->groupOrder)->toBe(0);
	expect($first->categoryOrder)->toBe(0);
	expect($first->deleted)->toBeFalse();

	/** @var Category $third */
	$third = $result->items[2];
	expect($third->id)->toBe('C3');
	expect($third->groupId)->toBe('CG2');
	expect($third->groupOrder)->toBe(1);
	expect($third->categoryOrder)->toBe(0);
});

it('skips categories with missing id', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['data' => ['category_groups' => [
			['id' => 'CG1', 'name' => 'Essentials', 'categories' => [
				['id' => '', 'name' => 'No ID', 'hidden' => false, 'deleted' => false],
				['id' => 'C1', 'name' => 'Groceries', 'hidden' => false, 'deleted' => false],
			]],
		]]])),
	]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$result = $client->categories('P1');

	expect($result->items)->toHaveCount(1);
	expect($result->items[0]->id)->toBe('C1');
});

it('handles groups with no categories array', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['data' => ['category_groups' => [
			['id' => 'CG1', 'name' => 'Empty Group'],
			['id' => 'CG2', 'name' => 'Normal', 'categories' => [
				['id' => 'C1', 'name' => 'Rent', 'hidden' => false, 'deleted' => false],
			]],
		]]])),
	]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$result = $client->categories('P1');

	expect($result->items)->toHaveCount(1);
	expect($result->items[0]->id)->toBe('C1');
});

it('returns empty collection for empty category_groups', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['data' => ['category_groups' => []]])),
	]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$result = $client->categories('P1');

	expect($result->items)->toHaveCount(0);
});

it('includes deleted categories and marks them correctly', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['data' => ['category_groups' => [
			['id' => 'CG1', 'name' => 'Essentials', 'categories' => [
				['id' => 'C1', 'name' => 'Groceries', 'hidden' => false, 'deleted' => false],
				['id' => 'C2', 'name' => 'Old Category', 'hidden' => false, 'deleted' => true],
			]],
		]]])),
	]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$result = $client->categories('P1');

	expect($result->items)->toHaveCount(2);
	expect($result->items[0]->deleted)->toBeFalse();
	expect($result->items[1]->deleted)->toBeTrue();
});

it('paginates categories across multiple pages', function () {
	$sender = new ArrayRequestSender([
		fn ($request) => new Response(200, [], json_encode(['data' => [
			'category_groups' => [
				['id' => 'CG1', 'name' => 'Essentials', 'categories' => [
					['id' => 'C1', 'name' => 'Groceries', 'hidden' => false, 'deleted' => false],
				]],
			],
			'server_knowledge' => 10,
			'next_page' => 2,
		]])),
		fn ($request) => new Response(200, [], json_encode(['data' => [
			'category_groups' => [
				['id' => 'CG2', 'name' => 'Savings', 'categories' => [
					['id' => 'C2', 'name' => 'Emergency', 'hidden' => false, 'deleted' => false],
				]],
			],
			'server_knowledge' => 11,
		]])),
	]);
	$client = YnabClient::withApiKey('api-key-123', requestSender: $sender);

	$result = $client->categories('P1');

	expect($result->items)->toHaveCount(2);
	expect($result->items[0]->id)->toBe('C1');
	expect($result->items[1]->id)->toBe('C2');
	expect($result->serverKnowledge)->toBe(11);
});

it('throws when pagination exceeds maxPages limit', function () {
	$pageResponse = fn ($request) => new Response(200, [], json_encode(['data' => [
		'category_groups' => [
			['id' => 'CG1', 'name' => 'G', 'categories' => [
				['id' => 'C1', 'name' => 'C', 'hidden' => false, 'deleted' => false],
			]],
		],
		'next_page' => 2,
	]]));

	$responses = array_fill(0, 105, $pageResponse);
	$sender = new ArrayRequestSender($responses);

	$config = new ClientConfig(baseUrl: 'https://api.ynab.com/v1', maxPages: 3);
	$client = YnabClient::withApiKey('api-key-123', config: $config, requestSender: $sender);

	expect(fn () => $client->categories('P1'))->toThrow(YnabException::class, 'Pagination limit of 3 pages exceeded.');
});
