<?php

declare(strict_types=1);

namespace JPry\YNAB\Tests\Fakes;

use JPry\YNAB\Http\Request;
use JPry\YNAB\Http\RequestSender;
use JPry\YNAB\Http\Response;
use RuntimeException;

final class ArrayRequestSender implements RequestSender
{
	/** @var array<int,Request> */
	public array $requests = [];

	/** @var list<callable(Request):Response> */
	private array $handlers;

	/** @param list<callable(Request):Response> $handlers */
	public function __construct(array $handlers)
	{
		$this->handlers = $handlers;
	}

	public function send(Request $request): Response
	{
		$this->requests[] = $request;

		$handler = array_shift($this->handlers);
		if ($handler === null) {
			throw new RuntimeException("No fake handler available for request: {$request->method} {$request->url}");
		}

		return $handler($request);
	}
}
