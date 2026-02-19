<?php

declare(strict_types=1);

namespace JPry\YNAB\OAuth;

final readonly class OAuthTokens
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken = null,
        public ?int $expiresIn = null,
        public string $tokenType = 'Bearer',
    ) {
    }

    /** @param array<string,mixed> $payload */
    public static function fromArray(array $payload): ?self
    {
        $accessToken = trim((string) ($payload['access_token'] ?? ''));
        if ($accessToken === '') {
            return null;
        }

        $refreshToken = isset($payload['refresh_token']) ? trim((string) $payload['refresh_token']) : null;
        if ($refreshToken === '') {
            $refreshToken = null;
        }

        $expiresIn = is_numeric($payload['expires_in'] ?? null) ? (int) $payload['expires_in'] : null;

        return new self(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: $expiresIn,
            tokenType: (string) ($payload['token_type'] ?? 'Bearer'),
        );
    }
}
