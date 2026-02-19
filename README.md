# JPry YNAB

Framework-agnostic PHP library for the YNAB API.

## Highlights

- Namespace root: `JPry\\YNAB\\`
- Typed resources for budgets, accounts, categories, payees, and transactions
- Endpoint-by-name client methods (`budgets()`, `transactions()`, etc.)
- Structured YNAB errors (`error.id`, `error.name`, `error.detail`)
- Supports API key auth and OAuth token auth
- Default request sender uses Guzzle, but any codebase can provide its own sender by implementing `JPry\\YNAB\\Http\\RequestSender`

## Install

```bash
composer require jpry/ynab
```

If you want the built-in default sender:

```bash
composer require guzzlehttp/guzzle
```

## Quick start

### API key

```php
use JPry\YNAB\Client\YnabClient;

$client = YnabClient::withApiKey('your-api-key');
$budgets = $client->budgets();
$transactions = $client->transactions('budget-id', ['since_date' => '2026-01-01']);
```

### OAuth token

```php
use JPry\YNAB\Client\YnabClient;

$client = YnabClient::withOAuthToken(
    accessToken: 'access-token',
    refreshAccessToken: fn (): string => 'new-access-token',
);
```

## OAuth flow helper

```php
use JPry\YNAB\Http\GuzzleRequestSender;
use JPry\YNAB\OAuth\OAuthClient;
use JPry\YNAB\OAuth\OAuthConfig;

$oauth = new OAuthClient(
    new OAuthConfig(
        clientId: 'client-id',
        clientSecret: 'client-secret',
        redirectUri: 'https://example.com/oauth/callback',
    ),
    new GuzzleRequestSender(baseUrl: 'https://api.ynab.com/v1'),
);

$authUrl = $oauth->authorizationUrl('state-value');
$tokens = $oauth->exchangeCodeForTokens('code-from-callback');
```

## Using your own HTTP client

```php
use JPry\YNAB\Http\Request;
use JPry\YNAB\Http\RequestSender;
use JPry\YNAB\Http\Response;

final class MySender implements RequestSender
{
    public function send(Request $request): Response
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
