<?php

declare(strict_types=1);

namespace JPry\YNAB\Http;

use JPry\YNAB\Exception\YnabException;

final class GuzzleRequestSender implements RequestSender
{
    private object $client;

    public function __construct(
        string $baseUrl = 'https://api.ynab.com/v1',
        int $timeoutSeconds = 30,
    ) {
        if (!class_exists('GuzzleHttp\\Client')) {
            throw new YnabException('guzzlehttp/guzzle is required for GuzzleRequestSender. Install it or provide a custom RequestSender implementation.');
        }

        $clientClass = 'GuzzleHttp\\Client';
        $this->client = new $clientClass([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'timeout' => $timeoutSeconds,
        ]);
    }

    public function send(Request $request): Response
    {
        $options = [
            'headers' => $request->headers,
            'query' => $request->query,
        ];

        if ($request->json !== null) {
            $options['json'] = $request->json;
        }

        if ($request->form !== null) {
            $options['form_params'] = $request->form;
        }

        try {
            $response = $this->client->request($request->method, $request->url, $options);
        } catch (\Throwable $e) {
            if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
                $response = $e->getResponse();
                return new Response(
                    statusCode: (int) $response->getStatusCode(),
                    headers: $this->flattenHeaders((array) $response->getHeaders()),
                    body: (string) $response->getBody(),
                );
            }

            throw new YnabException($e->getMessage() !== '' ? $e->getMessage() : 'HTTP request failed.', previous: $e);
        }

        return new Response(
            statusCode: (int) $response->getStatusCode(),
            headers: $this->flattenHeaders((array) $response->getHeaders()),
            body: (string) $response->getBody(),
        );
    }

    /** @param array<string,array<int,string>> $headers */
    /** @return array<string,string> */
    private function flattenHeaders(array $headers): array
    {
        $flat = [];
        foreach ($headers as $name => $values) {
            if (!is_string($name) || !is_array($values)) {
                continue;
            }
            $flat[strtolower($name)] = implode(',', $values);
        }

        return $flat;
    }
}
