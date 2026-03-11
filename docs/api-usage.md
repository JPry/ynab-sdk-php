# API Usage

## Common read methods

```php
$plans = $client->plans();
$plan = $client->plan('plan-id');
$settings = $client->planSettings('plan-id');

$accounts = $client->accounts('plan-id');
$payees = $client->payees('plan-id');
$categoryGroups = $client->categoryGroups('plan-id');

$months = $client->months('plan-id');
$month = $client->month('plan-id', '2026-03-01');

$transactions = $client->transactions('plan-id', ['since_date' => '2026-01-01']);
$transaction = $client->transaction('plan-id', 'transaction-id');
$scheduled = $client->scheduledTransactions('plan-id');
```

## Filtering and incremental sync

Use query parameters such as `since_date`, `type`, or `last_knowledge_of_server` where supported.

```php
$transactions = $client->transactions('plan-id', [
    'since_date' => '2026-01-01',
    'last_knowledge_of_server' => 1234,
]);

$scheduled = $client->scheduledTransactions('plan-id', [
    'last_knowledge_of_server' => 1234,
]);
```

Collection methods automatically follow pagination metadata (`next_page`, `next_page_url`, `links.next`).

## Category detail by month

```php
$current = $client->category('plan-id', 'category-id');
$march = $client->monthCategory('plan-id', '2026-03-01', 'category-id');
```

## Payee locations

```php
$locations = $client->payeeLocations('plan-id');
$singleLocation = $client->payeeLocation('plan-id', 'payee-location-id');
$locationsByPayee = $client->payeeLocationsByPayee('plan-id', 'payee-id');
```
