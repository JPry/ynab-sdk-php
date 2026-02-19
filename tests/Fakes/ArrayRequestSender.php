<?php

declare(strict_types=1);

namespace JPry\YNAB\Tests\Fakes;

use JPry\YNAB\Http\RequestSender;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class ArrayRequestSender implements RequestSender
{
	/** @var array<int,RequestInterface> */
	public array $requests = [];

	/** @var list<callable(RequestInterface):ResponseInterface> */
	private array $handlers;

	/** @param list<callable(RequestInterface):ResponseInterface> $handlers */
	public function __construct(array $handlers)
	{
		$this->handlers = $handlers;
	}

	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		$this->requests[] = $request;

		$handler = array_shift($this->handlers);
		if ($handler === null) {
			throw new RuntimeException("No fake handler available for request: {$request->getMethod()} {$request->getUri()}");
		}

		return $handler($request);
	}
}
