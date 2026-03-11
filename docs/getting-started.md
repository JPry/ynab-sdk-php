# Getting Started

## Install

```bash
composer require jpry/ynab-sdk-php
```

Install Guzzle if you want the built-in sender:

```bash
composer require guzzlehttp/guzzle
```

## Create a client

```php
use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Config\ClientConfig;

$config = new ClientConfig(
    baseUrl: 'https://api.ynab.com/v1',
    timeoutSeconds: 30,
    maxRetries: 2,
);

$client = YnabClient::withApiKey('your-api-key', config: $config);
```

## Most common first calls

```php
$user = $client->user();
$plans = $client->plans();
$defaultPlan = $client->defaultPlan();

if ($defaultPlan === null) {
    throw new RuntimeException('No default plan found.');
}

$planId = $defaultPlan->id;
$settings = $client->planSettings($planId);
$transactions = $client->transactions($planId, ['since_date' => '2026-01-01']);
$categories = $client->categories($planId);
```

## Handle YNAB API errors

```php
use JPry\YNAB\Exception\YnabApiException;

try {
    $plans = $client->plans();
} catch (YnabApiException $e) {
    // Useful for logging and retry logic.
    $status = $e->statusCode;
    $errorId = $e->errorId;
    $errorName = $e->errorName;
    $detail = $e->errorDetail;
}
```

## Next guides

- Continue with [API Usage](./api-usage.md)
- For writes, see [Mutations and Request Models](./mutations.md)
- For OAuth integrations, see [OAuth Flow](./oauth-flow.md)
