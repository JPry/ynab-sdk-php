<?php

declare(strict_types=1);

namespace JPry\YNAB\Auth;

final class OAuthTokenAuth implements AuthMethod
{
    /** @var null|callable():string */
    private $refreshAccessToken;

    public function __construct(
        private string $accessToken,
        ?callable $refreshAccessToken = null,
    ) {
        $this->refreshAccessToken = $refreshAccessToken;
    }

    public function apply(array $headers): array
    {
        $headers['Authorization'] = 'Bearer ' . $this->accessToken;

        return $headers;
    }

    public function rotateToken(): ?string
    {
        if ($this->refreshAccessToken === null) {
            return null;
        }

        $token = trim((string) ($this->refreshAccessToken)());
        if ($token === '') {
            return null;
        }

        $this->accessToken = $token;

        return $this->accessToken;
    }
}
