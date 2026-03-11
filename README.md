# JPry YNAB

Framework-agnostic PHP library for the YNAB API.

Repository: [jpry/ynab-sdk-php](https://github.com/JPry/ynab-sdk-php)

## Highlights

- Namespace root: `JPry\\YNAB\\`
- Typed resources for users, plans, plan settings, accounts, categories, category groups, payees, payee locations, months, money movements, scheduled transactions, and transactions
- Endpoint-by-name client methods (`user()`, `plans()`, `planSettings()`, `category()`, `monthCategory()`, `months()`, `moneyMovements()`, `scheduledTransactions()`, etc.)
- Structured YNAB errors (`error.id`, `error.name`, `error.detail`)
- Supports API key auth and OAuth token auth
- Uses PSR-7/PSR-18 HTTP contracts; any codebase can provide its own sender by implementing `JPry\\YNAB\\Http\\RequestSender`

## Install

```bash
composer require jpry/ynab-sdk-php
```

If you want the built-in default sender:

```bash
composer require guzzlehttp/guzzle
```

## Quick start

### API key

```php
use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Config\ClientConfig;

$config = new ClientConfig(
    baseUrl: 'https://api.ynab.com/v1',
    timeoutSeconds: 30,
    maxRetries: 2,
);

$client = YnabClient::withApiKey('your-api-key', config: $config);
$user = $client->user();
$plans = $client->plans();
$settings = $client->planSettings('plan-id');
$months = $client->months('plan-id');
$moneyMovements = $client->moneyMovements('plan-id');
$scheduled = $client->scheduledTransactions('plan-id');
$payeeLocations = $client->payeeLocations('plan-id');
$category = $client->category('plan-id', 'category-id');
$monthCategory = $client->monthCategory('plan-id', '2026-03-01', 'category-id');
$transactions = $client->transactions('plan-id', ['since_date' => '2026-01-01']);
```

Legacy budget-named APIs (`budgets()`, `defaultBudget()`, and `JPry\\YNAB\\Model\\Budget`) remain available for backward compatibility but are deprecated and emit `E_USER_DEPRECATED` warnings.

Most mutating methods (create/update/delete) return the raw `data` payload from the API response as `array<string,mixed>`.
Mutating methods also accept typed request models in `JPry\\YNAB\\Model\\Mutation\\*` (legacy array and string-id signatures remain supported).

```php
use JPry\YNAB\Model\Mutation\CreateTransactionsRequest;
use JPry\YNAB\Model\Mutation\TransactionPayload;
use JPry\YNAB\Model\Mutation\UpdateTransactionRequest;

$client->createTransactions(
    'plan-id',
    CreateTransactionsRequest::single(
        new TransactionPayload(accountId: 'account-id', amount: -1000)
    ),
);

$client->updateTransaction(
    'plan-id',
    new UpdateTransactionRequest('transaction-id', new TransactionPayload(memo: 'Updated memo')),
);
```

## Documentation

- [Documentation Index](./docs/README.md)
- [Getting Started](./docs/getting-started.md)
- [API Usage](./docs/api-usage.md)
- [Mutations and Request Models](./docs/mutations.md)
- [OAuth Flow](./docs/oauth-flow.md)
- [Migration Notes: Budgets to Plans](./docs/migration-plans.md)
- [OpenAPI Spec Snapshot](./openapi/README.md)

### OAuth token

```php
use JPry\YNAB\Client\YnabClient;
use JPry\YNAB\Config\ClientConfig;

$config = new ClientConfig();
$client = YnabClient::withOAuthToken(
    accessToken: 'access-token',
    refreshAccessToken: fn (): string => 'new-access-token',
    config: $config,
);
```

## OAuth flow helper

```php
use JPry\YNAB\Http\GuzzleRequestSender;
use JPry\YNAB\OAuth\OAuthClient;
use JPry\YNAB\OAuth\OAuthConfig;
use JPry\YNAB\Config\ClientConfig;

$oauth = new OAuthClient(
    new OAuthConfig(
        clientId: 'client-id',
        clientSecret: 'client-secret',
        redirectUri: 'https://example.com/oauth/callback',
    ),
    new GuzzleRequestSender(new ClientConfig()),
);

$authUrl = $oauth->authorizationUrl('state-value');
$tokens = $oauth->exchangeCodeForTokens('code-from-callback');
```

## Using your own HTTP client

```php
use JPry\YNAB\Http\RequestSender;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

final class MySender implements RequestSender
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // Bridge to your own HTTP library
        return new Response(200, [], '{"data":{}}');
    }
}
```

Then pass it into factory methods:

```php
$client = YnabClient::withApiKey('api-key', requestSender: new MySender());
```

## Pagination

Collection methods automatically follow pagination metadata when present (`next_page`, `next_page_url`, or `links.next`).

## OpenAPI spec

The repository includes a local snapshot of the YNAB OpenAPI specification at `openapi/ynab-v1.openapi.yaml`.

- Sync manually: `./scripts/sync-openapi-spec.sh`
- Automated sync: `.github/workflows/openapi-spec-sync.yml` (weekly + manual dispatch)

## Tests

```bash
composer test
```
