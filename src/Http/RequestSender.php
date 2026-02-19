<?php

declare(strict_types=1);

namespace JPry\YNAB\Http;

interface RequestSender
{
    public function send(Request $request): Response;
}
