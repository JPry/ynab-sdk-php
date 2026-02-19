<?php

declare(strict_types=1);

namespace GuzzleHttp;

class Client
{
	/** @param array<string,mixed> $config */
	public function __construct(array $config = [])
	{
	}

	/** @return mixed */
	public function send($request)
	{
	}
}

namespace GuzzleHttp\Exception;

interface GuzzleException extends \Throwable
{
}
