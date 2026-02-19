<?php

declare(strict_types=1);

use JPry\YNAB\Model\Budget;
use JPry\YNAB\Model\Transaction;

it('maps budget rows into typed objects', function () {
    $budget = Budget::fromArray(['id' => 'B1', 'name' => 'Main']);

    expect($budget)->not->toBeNull();
    expect($budget?->id)->toBe('B1');
    expect($budget?->name)->toBe('Main');
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
