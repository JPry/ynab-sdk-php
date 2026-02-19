<?php

declare(strict_types=1);

namespace JPry\YNAB\Auth;

interface AuthMethod
{
    /** @param array<string,string> $headers */
    /** @return array<string,string> */
    public function apply(array $headers): array;
}
