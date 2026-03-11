# OAuth Flow

Use `OAuthClient` to generate authorization URLs and exchange tokens.
Use `YnabClient::withOAuthToken()` for authenticated API calls.

## Build authorization URL

```php
use JPry\YNAB\Config\ClientConfig;
use JPry\YNAB\Http\GuzzleRequestSender;
use JPry\YNAB\OAuth\OAuthClient;
use JPry\YNAB\OAuth\OAuthConfig;

$oauth = new OAuthClient(
    new OAuthConfig(
        clientId: 'client-id',
        clientSecret: 'client-secret',
        redirectUri: 'https://example.com/oauth/callback',
    ),
    new GuzzleRequestSender(new ClientConfig()),
);

$authorizationUrl = $oauth->authorizationUrl('csrf-state-value');
```

## Exchange callback code for tokens

```php
$tokens = $oauth->exchangeCodeForTokens($_GET['code']);

$accessToken = $tokens->accessToken;
$refreshToken = $tokens->refreshToken;
$expiresIn = $tokens->expiresIn;
```

## Use access token and refresh callback

```php
use JPry\YNAB\Client\YnabClient;

$client = YnabClient::withOAuthToken(
    accessToken: $storedAccessToken,
    refreshAccessToken: function (): string {
        // Refresh with your token store and return the new access token.
        return 'new-access-token';
    },
);

$plans = $client->plans();
```

## Notes

- Persist and rotate refresh tokens using your secure credential store.
- Keep your redirect URI exact and consistent between app settings and runtime config.
