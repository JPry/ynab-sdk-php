<?php

declare(strict_types=1);

namespace JPry\YNAB\OAuth;

use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\Http\Request;
use JPry\YNAB\Http\RequestSender;

final readonly class OAuthClient
{
    public function __construct(
        private OAuthConfig $config,
        private RequestSender $requestSender,
    ) {
    }

    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => $this->config->clientId,
            'redirect_uri' => $this->config->redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ]);

        return rtrim($this->config->authorizeUrl, '?') . '?' . $query;
    }

    public function exchangeCodeForTokens(string $code): OAuthTokens
    {
        return $this->tokenRequest([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'redirect_uri' => $this->config->redirectUri,
        ]);
    }

    public function refreshAccessToken(string $refreshToken): OAuthTokens
    {
        return $this->tokenRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
        ]);
    }

    /** @param array<string,string> $form */
    private function tokenRequest(array $form): OAuthTokens
    {
        $response = $this->requestSender->send(
            new Request(
                method: 'POST',
                url: $this->config->tokenUrl,
                headers: ['Accept' => 'application/json'],
                form: $form,
            ),
        );

        $decoded = $response->json();
        $tokens = OAuthTokens::fromArray($decoded);

        if ($tokens === null) {
            throw new YnabException('Could not parse OAuth token response.');
        }

        return $tokens;
    }
}
