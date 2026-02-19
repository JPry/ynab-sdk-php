<?php

declare(strict_types=1);

use JPry\YNAB\Internal\YnabErrorParser;

it('parses ynab structured error payload', function () {
    $parsed = YnabErrorParser::parse(
        '{"error":{"id":"403.1","name":"subscription_lapsed","detail":"Subscription lapsed."}}',
    );

    expect($parsed['id'])->toBe('403.1');
    expect($parsed['name'])->toBe('subscription_lapsed');
    expect($parsed['detail'])->toBe('Subscription lapsed.');
});

it('returns null fields when payload has no error object', function () {
    $parsed = YnabErrorParser::parse('{"data":{}}');

    expect($parsed)->toBe([
        'id' => null,
        'name' => null,
        'detail' => null,
    ]);
});
