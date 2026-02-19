<?php

declare(strict_types=1);

namespace JPry\YNAB\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JPry\YNAB\Config\ClientConfig;
use JPry\YNAB\Exception\YnabException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class GuzzleRequestSender implements RequestSender
{
	private Client $client;

	public function __construct(
		private readonly ClientConfig $config = new ClientConfig(),
	) {
		if (!class_exists(Client::class)) {
			throw new YnabException('guzzlehttp/guzzle is required for GuzzleRequestSender. Install it or provide a custom RequestSender implementation.');
		}

		$normalizedBaseUrl = rtrim($this->config->baseUrl, '/');
		$this->client = new Client([
			'base_uri' => "{$normalizedBaseUrl}/",
			'timeout' => $this->config->timeoutSeconds,
			'http_errors' => false,
		]);
	}

	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		try {
			return $this->client->send($request);
		} catch (GuzzleException $e) {
			throw new YnabException($e->getMessage() !== '' ? $e->getMessage() : 'HTTP request failed.', previous: $e);
		}
	}
}
