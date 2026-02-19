<?php

declare(strict_types=1);

namespace JPry\YNAB\Client;

use JPry\YNAB\Auth\ApiKeyAuth;
use JPry\YNAB\Auth\AuthMethod;
use JPry\YNAB\Auth\OAuthTokenAuth;
use JPry\YNAB\Config\ClientConfig;
use JPry\YNAB\Exception\YnabApiException;
use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\Http\GuzzleRequestSender;
use JPry\YNAB\Http\Request;
use JPry\YNAB\Http\RequestSender;
use JPry\YNAB\Internal\YnabErrorParser;
use JPry\YNAB\Model\Account;
use JPry\YNAB\Model\Budget;
use JPry\YNAB\Model\Category;
use JPry\YNAB\Model\Payee;
use JPry\YNAB\Model\ResourceCollection;
use JPry\YNAB\Model\Transaction;

final class YnabClient
{
    public function __construct(
        private readonly RequestSender $requestSender,
        private readonly AuthMethod $auth,
        private readonly ClientConfig $config = new ClientConfig(),
    ) {
    }

    public static function withApiKey(string $apiKey, ?RequestSender $requestSender = null, ?ClientConfig $config = null): self
    {
        $config ??= new ClientConfig();
        $requestSender ??= new GuzzleRequestSender($config->baseUrl, $config->timeoutSeconds);

        return new self($requestSender, new ApiKeyAuth($apiKey), $config);
    }

    /** @param null|callable():string $refreshAccessToken */
    public static function withOAuthToken(string $accessToken, ?callable $refreshAccessToken = null, ?RequestSender $requestSender = null, ?ClientConfig $config = null): self
    {
        $config ??= new ClientConfig();
        $requestSender ??= new GuzzleRequestSender($config->baseUrl, $config->timeoutSeconds);

        return new self($requestSender, new OAuthTokenAuth($accessToken, $refreshAccessToken), $config);
    }

    /** @return ResourceCollection<Budget> */
    public function budgets(array $query = []): ResourceCollection
    {
        return $this->collection('/budgets', $query, 'budgets', static fn (array $row): ?Budget => Budget::fromArray($row));
    }

    public function defaultBudget(): ?Budget
    {
        $data = $this->get('/budgets/default');
        return is_array($data['budget'] ?? null) ? Budget::fromArray($data['budget']) : null;
    }

    /** @return ResourceCollection<Account> */
    public function accounts(string $budgetId, array $query = []): ResourceCollection
    {
        return $this->collection("/budgets/{$budgetId}/accounts", $query, 'accounts', static fn (array $row): ?Account => Account::fromArray($row));
    }

    /** @return ResourceCollection<Category> */
    public function categories(string $budgetId, array $query = []): ResourceCollection
    {
        $items = [];
        $serverKnowledge = null;
        $nextQuery = $query;

        while (true) {
            $data = $this->get("/budgets/{$budgetId}/categories", $nextQuery);
            $serverKnowledge = $this->serverKnowledge($data) ?? $serverKnowledge;

            $groups = $data['category_groups'] ?? [];
            if (is_array($groups)) {
                foreach ($groups as $groupOrder => $group) {
                    if (!is_array($group)) {
                        continue;
                    }

                    $groupId = (string) ($group['id'] ?? '');
                    $groupName = (string) ($group['name'] ?? '');
                    $categories = $group['categories'] ?? [];
                    if (!is_array($categories)) {
                        continue;
                    }

                    foreach ($categories as $categoryOrder => $category) {
                        if (!is_array($category)) {
                            continue;
                        }

                        $id = trim((string) ($category['id'] ?? ''));
                        if ($id === '') {
                            continue;
                        }

                        $items[] = new Category(
                            id: $id,
                            name: (string) ($category['name'] ?? ''),
                            groupId: $groupId,
                            groupName: $groupName,
                            groupOrder: (int) $groupOrder,
                            categoryOrder: (int) $categoryOrder,
                            hidden: (bool) ($category['hidden'] ?? false),
                        );
                    }
                }
            }

            $nextPageQuery = $this->nextPageQuery($data);
            if ($nextPageQuery === null) {
                break;
            }

            $nextQuery = array_merge($query, $nextPageQuery);
        }

        return new ResourceCollection($items, $serverKnowledge);
    }

    /** @return ResourceCollection<Payee> */
    public function payees(string $budgetId, array $query = []): ResourceCollection
    {
        return $this->collection("/budgets/{$budgetId}/payees", $query, 'payees', static fn (array $row): ?Payee => Payee::fromArray($row));
    }

    /** @return ResourceCollection<Transaction> */
    public function transactions(string $budgetId, array $query = []): ResourceCollection
    {
        return $this->collection("/budgets/{$budgetId}/transactions", $query, 'transactions', static fn (array $row): ?Transaction => Transaction::fromArray($row));
    }

    /** @param array<string,mixed> $payload */
    /** @return array<string,mixed> */
    public function patchTransactions(string $budgetId, array $payload): array
    {
        return $this->request('PATCH', "/budgets/{$budgetId}/transactions", [], $payload);
    }

    /** @return array<string,mixed> */
    private function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, $query, null);
    }

    /** @return array<string,mixed> */
    private function request(string $method, string $path, array $query, ?array $json): array
    {
        $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : rtrim($this->config->baseUrl, '/') . '/' . ltrim($path, '/');

        $headers = $this->auth->apply([
            'Accept' => 'application/json',
        ]);

        $attempts = 0;

        do {
            $attempts++;
            $response = $this->requestSender->send(new Request(
                method: $method,
                url: $url,
                headers: $headers,
                query: $query,
                json: $json,
            ));

            if ($response->statusCode === 401 && $this->auth instanceof OAuthTokenAuth && $attempts === 1) {
                $newToken = $this->auth->rotateToken();
                if ($newToken !== null) {
                    $headers = $this->auth->apply(['Accept' => 'application/json']);
                    continue;
                }
            }

            if ($response->statusCode >= 200 && $response->statusCode < 300) {
                $decoded = $response->json();
                if (!is_array($decoded['data'] ?? null)) {
                    throw new YnabException('Unexpected response format from YNAB API.');
                }

                return $decoded['data'];
            }

            throw $this->apiException($method, $path, $response);
        } while ($attempts <= max(1, $this->config->maxRetries));

        throw new YnabException('YNAB request failed after retry attempts.');
    }

    private function apiException(string $method, string $path, \JPry\YNAB\Http\Response $response): YnabApiException
    {
        $error = YnabErrorParser::parse($response->body);

        $retryAfter = null;
        if (is_numeric($response->headers['retry-after'] ?? null)) {
            $retryAfter = (int) $response->headers['retry-after'];
        }

        $identifier = implode(' ', array_values(array_filter([
            $error['id'],
            $error['name'],
        ], static fn (?string $v): bool => $v !== null && $v !== '')));

        $idSuffix = $identifier !== '' ? " {$identifier}" : '';
        $detailSuffix = $error['detail'] !== null ? " {$error['detail']}" : '';

        return new YnabApiException(
            message: "YNAB API request failed ({$method} {$path}, HTTP {$response->statusCode}).{$idSuffix}{$detailSuffix}",
            statusCode: $response->statusCode,
            retryAfterSeconds: $retryAfter,
            errorId: $error['id'],
            errorName: $error['name'],
            errorDetail: $error['detail'],
        );
    }

    /** @template T */
    /** @param callable(array<string,mixed>):?T $mapper */
    /** @return ResourceCollection<T> */
    private function collection(string $path, array $query, string $key, callable $mapper): ResourceCollection
    {
        $items = [];
        $serverKnowledge = null;
        $nextQuery = $query;

        while (true) {
            $data = $this->get($path, $nextQuery);
            $serverKnowledge = $this->serverKnowledge($data) ?? $serverKnowledge;

            $rows = $data[$key] ?? [];
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $mapped = $mapper($row);
                    if ($mapped !== null) {
                        $items[] = $mapped;
                    }
                }
            }

            $nextPageQuery = $this->nextPageQuery($data);
            if ($nextPageQuery === null) {
                break;
            }

            $nextQuery = array_merge($query, $nextPageQuery);
        }

        return new ResourceCollection($items, $serverKnowledge);
    }

    /** @param array<string,mixed> $data */
    private function serverKnowledge(array $data): ?int
    {
        $value = $data['server_knowledge'] ?? null;
        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /** @param array<string,mixed> $data */
    /** @return array<string,string>|null */
    private function nextPageQuery(array $data): ?array
    {
        $nextPage = $data['next_page'] ?? null;
        if ($nextPage !== null && (is_int($nextPage) || is_string($nextPage))) {
            $value = trim((string) $nextPage);
            if ($value !== '') {
                return ['page' => $value];
            }
        }

        $nextPageUrl = $data['next_page_url'] ?? null;
        if (is_string($nextPageUrl) && $nextPageUrl !== '') {
            return $this->queryFromUrl($nextPageUrl);
        }

        $links = $data['links'] ?? null;
        if (is_array($links) && is_string($links['next'] ?? null)) {
            return $this->queryFromUrl((string) $links['next']);
        }

        return null;
    }

    /** @return array<string,string> */
    private function queryFromUrl(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return [];
        }

        parse_str($query, $parsed);
        if (!is_array($parsed)) {
            return [];
        }

        $normalized = [];
        foreach ($parsed as $key => $value) {
            if (!is_string($key) || !is_scalar($value)) {
                continue;
            }
            $normalized[$key] = (string) $value;
        }

        return $normalized;
    }
}
