<?php

declare(strict_types=1);

namespace JPry\YNAB\Internal;

final class YnabErrorParser
{
    /** @return array{id:?string,name:?string,detail:?string} */
    public static function parse(string $body): array
    {
        $decoded = json_decode($body, true);
        if (!is_array($decoded) || !is_array($decoded['error'] ?? null)) {
            return ['id' => null, 'name' => null, 'detail' => null];
        }

        $error = $decoded['error'];

        $id = trim((string) ($error['id'] ?? ''));
        $name = trim((string) ($error['name'] ?? ''));
        $detail = trim((string) ($error['detail'] ?? ''));

        return [
            'id' => $id !== '' ? $id : null,
            'name' => $name !== '' ? $name : null,
            'detail' => $detail !== '' ? $detail : null,
        ];
    }
}
