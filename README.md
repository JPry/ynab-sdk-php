# JPry YNAB

Framework-agnostic PHP library for the YNAB API.

Repository: [jpry/ynab-sdk-php](https://github.com/JPry/ynab-sdk-php)

## Highlights

- Namespace root: `JPry\\YNAB\\`
- Typed resources for budgets, accounts, categories, payees, and transactions
- Endpoint-by-name client methods (`budgets()`, `transactions()`, etc.)
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
$budgets = $client->budgets();
$transactions = $client->transactions('budget-id', ['since_date' => '2026-01-01']);
```

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

## Tests

```bash
composer test
```
