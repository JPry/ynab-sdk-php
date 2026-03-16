<?php

declare(strict_types=1);

namespace JPry\YNAB\Client;

use GuzzleHttp\Psr7\Request;
use JPry\YNAB\Auth\ApiKeyAuth;
use JPry\YNAB\Auth\AuthMethod;
use JPry\YNAB\Auth\OAuthTokenAuth;
use JPry\YNAB\Config\ClientConfig;
use JPry\YNAB\Exception\YnabApiException;
use JPry\YNAB\Exception\YnabException;
use JPry\YNAB\Http\GuzzleRequestSender;
use JPry\YNAB\Http\RequestSender;
use JPry\YNAB\Internal\BudgetDeprecationWarningTrait;
use JPry\YNAB\Internal\YnabErrorParser;
use JPry\YNAB\Model\Account;
use JPry\YNAB\Model\Budget;
use JPry\YNAB\Model\Category;
use JPry\YNAB\Model\CategoryDetail;
use JPry\YNAB\Model\CategoryGroup;
use JPry\YNAB\Model\Month;
use JPry\YNAB\Model\Mutation\CreateAccountRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryRequest;
use JPry\YNAB\Model\Mutation\CreateScheduledTransactionRequest;
use JPry\YNAB\Model\Mutation\CreateTransactionsRequest;
use JPry\YNAB\Model\Mutation\ImportTransactionsRequest;
use JPry\YNAB\Model\Mutation\PatchTransactionsRequest;
use JPry\YNAB\Model\Mutation\RequestModel;
use JPry\YNAB\Model\Mutation\UpdateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\UpdateCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdateMonthCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdatePayeeRequest;
use JPry\YNAB\Model\Mutation\UpdateScheduledTransactionRequest;
use JPry\YNAB\Model\Mutation\UpdateTransactionRequest;
use JPry\YNAB\Model\MoneyMovement;
use JPry\YNAB\Model\MoneyMovementGroup;
use JPry\YNAB\Model\Payee;
use JPry\YNAB\Model\PayeeLocation;
use JPry\YNAB\Model\Plan;
use JPry\YNAB\Model\PlanSettings;
use JPry\YNAB\Model\ResourceCollection;
use JPry\YNAB\Model\ScheduledTransaction;
use JPry\YNAB\Model\Transaction;
use JPry\YNAB\Model\User;
use Psr\Http\Message\ResponseInterface;

final readonly class YnabClient
{
	use BudgetDeprecationWarningTrait;

	public function __construct(
		private RequestSender $requestSender,
		private AuthMethod $auth,
		private ClientConfig $config = new ClientConfig(),
	) {
	}

	public static function withApiKey(string $apiKey, ?RequestSender $requestSender = null, ?ClientConfig $config = null): self
	{
		$config ??= new ClientConfig();
		$requestSender ??= new GuzzleRequestSender($config);

		return new self($requestSender, new ApiKeyAuth($apiKey), $config);
	}

	/** @param null|callable():string $refreshAccessToken */
	public static function withOAuthToken(string $accessToken, ?callable $refreshAccessToken = null, ?RequestSender $requestSender = null, ?ClientConfig $config = null): self
	{
		$config ??= new ClientConfig();
		$requestSender ??= new GuzzleRequestSender($config);

		return new self($requestSender, new OAuthTokenAuth($accessToken, $refreshAccessToken), $config);
	}

	/**
	 * @deprecated YNAB API v1.79.0 renamed budgets to plans. Use plans() instead.
	 * @return ResourceCollection<Budget>
	 */
	public function budgets(array $query = []): ResourceCollection
	{
		$this->warnBudgetDeprecation('budgets()', 'plans()');

		return $this->collection('/plans', $query, ['plans', 'budgets'], static fn (array $row): ?Budget => Budget::fromArray($row));
	}

	/** @return ResourceCollection<Plan> */
	public function plans(array $query = []): ResourceCollection
	{
		return $this->collection('/plans', $query, ['plans', 'budgets'], static fn (array $row): ?Plan => Plan::fromArray($row));
	}

	/**
	 * @deprecated YNAB API v1.79.0 renamed default_budget to default_plan. Use defaultPlan() instead.
	 */
	public function defaultBudget(): ?Budget
	{
		$this->warnBudgetDeprecation('defaultBudget()', 'defaultPlan()');

		$data = $this->get('/plans/default');
		$default = $this->firstArrayByKeys($data, ['budget', 'plan']);

		return $default === null ? null : Budget::fromArray($default);
	}

	public function defaultPlan(): ?Plan
	{
		$data = $this->get('/plans/default');
		$default = $this->firstArrayByKeys($data, ['plan', 'budget']);

		return $default === null ? null : Plan::fromArray($default);
	}

	public function user(): ?User
	{
		$data = $this->get('/user');
		return is_array($data['user'] ?? null) ? User::fromArray($data['user']) : null;
	}

	public function planSettings(string $planId): ?PlanSettings
	{
		$data = $this->get("/plans/{$planId}/settings");
		return is_array($data['settings'] ?? null) ? PlanSettings::fromArray($data['settings']) : null;
	}

	public function plan(string $planId): ?Plan
	{
		return $this->item("/plans/{$planId}", [], ['plan', 'budget'], static fn (array $row): ?Plan => Plan::fromArray($row));
	}

	public function account(string $planId, string $accountId): ?Account
	{
		return $this->item("/plans/{$planId}/accounts/{$accountId}", [], 'account', static fn (array $row): ?Account => Account::fromArray($row));
	}

	public function payee(string $planId, string $payeeId): ?Payee
	{
		return $this->item("/plans/{$planId}/payees/{$payeeId}", [], 'payee', static fn (array $row): ?Payee => Payee::fromArray($row));
	}

	/** @return ResourceCollection<CategoryGroup> */
	public function categoryGroups(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/category_groups", $query, 'category_groups', static fn (array $row): ?CategoryGroup => CategoryGroup::fromArray($row));
	}

	public function categoryGroup(string $planId, string $categoryGroupId): ?CategoryGroup
	{
		return $this->item("/plans/{$planId}/category_groups/{$categoryGroupId}", [], 'category_group', static fn (array $row): ?CategoryGroup => CategoryGroup::fromArray($row));
	}

	/** @return ResourceCollection<PayeeLocation> */
	public function payeeLocations(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/payee_locations", $query, 'payee_locations', static fn (array $row): ?PayeeLocation => PayeeLocation::fromArray($row));
	}

	public function payeeLocation(string $planId, string $payeeLocationId): ?PayeeLocation
	{
		return $this->item("/plans/{$planId}/payee_locations/{$payeeLocationId}", [], 'payee_location', static fn (array $row): ?PayeeLocation => PayeeLocation::fromArray($row));
	}

	/** @return ResourceCollection<PayeeLocation> */
	public function payeeLocationsByPayee(string $planId, string $payeeId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/payees/{$payeeId}/payee_locations", $query, 'payee_locations', static fn (array $row): ?PayeeLocation => PayeeLocation::fromArray($row));
	}

	/** @return ResourceCollection<Month> */
	public function months(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/months", $query, 'months', static fn (array $row): ?Month => Month::fromArray($row));
	}

	public function month(string $planId, string $month): ?Month
	{
		return $this->item("/plans/{$planId}/months/{$month}", [], 'month', static fn (array $row): ?Month => Month::fromArray($row));
	}

	/** @return ResourceCollection<MoneyMovement> */
	public function moneyMovements(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/money_movements", $query, 'money_movements', static fn (array $row): ?MoneyMovement => MoneyMovement::fromArray($row));
	}

	/** @return ResourceCollection<MoneyMovement> */
	public function moneyMovementsByMonth(string $planId, string $month, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/months/{$month}/money_movements", $query, 'money_movements', static fn (array $row): ?MoneyMovement => MoneyMovement::fromArray($row));
	}

	/** @return ResourceCollection<MoneyMovementGroup> */
	public function moneyMovementGroups(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/money_movement_groups", $query, 'money_movement_groups', static fn (array $row): ?MoneyMovementGroup => MoneyMovementGroup::fromArray($row));
	}

	/** @return ResourceCollection<MoneyMovementGroup> */
	public function moneyMovementGroupsByMonth(string $planId, string $month, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/months/{$month}/money_movement_groups", $query, 'money_movement_groups', static fn (array $row): ?MoneyMovementGroup => MoneyMovementGroup::fromArray($row));
	}

	public function transaction(string $planId, string $transactionId): ?Transaction
	{
		return $this->item("/plans/{$planId}/transactions/{$transactionId}", [], 'transaction', static fn (array $row): ?Transaction => Transaction::fromArray($row));
	}

	/** @return ResourceCollection<Transaction> */
	public function transactionsByAccount(string $planId, string $accountId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/accounts/{$accountId}/transactions", $query, 'transactions', static fn (array $row): ?Transaction => Transaction::fromArray($row));
	}

	/** @return ResourceCollection<Transaction> */
	public function transactionsByCategory(string $planId, string $categoryId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/categories/{$categoryId}/transactions", $query, 'transactions', static fn (array $row): ?Transaction => Transaction::fromArray($row));
	}

	/** @return ResourceCollection<Transaction> */
	public function transactionsByPayee(string $planId, string $payeeId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/payees/{$payeeId}/transactions", $query, 'transactions', static fn (array $row): ?Transaction => Transaction::fromArray($row));
	}

	/** @return ResourceCollection<Transaction> */
	public function transactionsByMonth(string $planId, string $month, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/months/{$month}/transactions", $query, 'transactions', static fn (array $row): ?Transaction => Transaction::fromArray($row));
	}

	/** @return ResourceCollection<ScheduledTransaction> */
	public function scheduledTransactions(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/scheduled_transactions", $query, 'scheduled_transactions', static fn (array $row): ?ScheduledTransaction => ScheduledTransaction::fromArray($row));
	}

	public function scheduledTransaction(string $planId, string $scheduledTransactionId): ?ScheduledTransaction
	{
		return $this->item("/plans/{$planId}/scheduled_transactions/{$scheduledTransactionId}", [], 'scheduled_transaction', static fn (array $row): ?ScheduledTransaction => ScheduledTransaction::fromArray($row));
	}

	/** @return ResourceCollection<Account> */
	public function accounts(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/accounts", $query, 'accounts', static fn (array $row): ?Account => Account::fromArray($row));
	}

	/** @return ResourceCollection<Category> */
	public function categories(string $planId, array $query = []): ResourceCollection
	{
		$items = [];
		$serverKnowledge = null;
		$nextQuery = $query;
		$pageCount = 0;

		while (true) {
			$pageCount++;
			if ($pageCount > $this->config->maxPages) {
				throw new YnabException("Pagination limit of {$this->config->maxPages} pages exceeded.");
			}

			$data = $this->get("/plans/{$planId}/categories", $nextQuery);
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

						$item = Category::fromArray($category, [
							'groupId' => $groupId,
							'groupName' => $groupName,
							'groupOrder' => (int) $groupOrder,
							'categoryOrder' => (int) $categoryOrder,
						]);
						if ($item === null) {
							continue;
						}

						$items[] = $item;
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

	public function category(string $planId, string $categoryId): ?CategoryDetail
	{
		return $this->item("/plans/{$planId}/categories/{$categoryId}", [], 'category', static fn (array $row): ?CategoryDetail => CategoryDetail::fromArray($row));
	}

	public function monthCategory(string $planId, string $month, string $categoryId): ?CategoryDetail
	{
		return $this->item("/plans/{$planId}/months/{$month}/categories/{$categoryId}", [], 'category', static fn (array $row): ?CategoryDetail => CategoryDetail::fromArray($row));
	}

	/** @return ResourceCollection<Payee> */
	public function payees(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/payees", $query, 'payees', static fn (array $row): ?Payee => Payee::fromArray($row));
	}

	/** @return ResourceCollection<Transaction> */
	public function transactions(string $planId, array $query = []): ResourceCollection
	{
		return $this->collection("/plans/{$planId}/transactions", $query, 'transactions', static fn (array $row): ?Transaction => Transaction::fromArray($row));
	}

	/**
	 * @param array<string,mixed>|PatchTransactionsRequest $payload
	 * @return array<string,mixed>
	 */
	public function patchTransactions(string $planId, array|PatchTransactionsRequest $payload): array
	{
		return $this->request('PATCH', "/plans/{$planId}/transactions", [], $this->payloadToArray($payload));
	}

	/**
	 * @param array<string,mixed>|CreateTransactionsRequest $payload
	 * @return array<string,mixed>
	 */
	public function createTransactions(string $planId, array|CreateTransactionsRequest $payload): array
	{
		return $this->request('POST', "/plans/{$planId}/transactions", [], $this->payloadToArray($payload));
	}

	/**
	 * @param null|array<string,mixed>|ImportTransactionsRequest $payload
	 * @return array<string,mixed>
	 */
	public function importTransactions(string $planId, array|ImportTransactionsRequest|null $payload = null): array
	{
		return $this->request('POST', "/plans/{$planId}/transactions/import", [], $payload === null ? null : $this->payloadToArray($payload));
	}

	/**
	 * @param UpdateTransactionRequest|string $transactionId
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function updateTransaction(string $planId, UpdateTransactionRequest|string $transactionId, ?array $payload = null): array
	{
		return $this->mutate('PUT', "/plans/{$planId}/transactions", 'updateTransaction', $transactionId, $payload);
	}

	/**
	 * @param string|Transaction|UpdateTransactionRequest $transactionId
	 * @return array<string,mixed>
	 */
	public function deleteTransaction(string $planId, string|Transaction|UpdateTransactionRequest $transactionId): array
	{
		return $this->request('DELETE', "/plans/{$planId}/transactions/{$this->resolveModelId($transactionId)}", [], null);
	}

	/**
	 * @param array<string,mixed>|CreateScheduledTransactionRequest $payload
	 * @return array<string,mixed>
	 */
	public function createScheduledTransaction(string $planId, array|CreateScheduledTransactionRequest $payload): array
	{
		return $this->request('POST', "/plans/{$planId}/scheduled_transactions", [], $this->payloadToArray($payload));
	}

	/**
	 * @param UpdateScheduledTransactionRequest|string $scheduledTransactionId
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function updateScheduledTransaction(string $planId, UpdateScheduledTransactionRequest|string $scheduledTransactionId, ?array $payload = null): array
	{
		return $this->mutate('PUT', "/plans/{$planId}/scheduled_transactions", 'updateScheduledTransaction', $scheduledTransactionId, $payload);
	}

	/**
	 * @param string|ScheduledTransaction|UpdateScheduledTransactionRequest $scheduledTransactionId
	 * @return array<string,mixed>
	 */
	public function deleteScheduledTransaction(string $planId, string|ScheduledTransaction|UpdateScheduledTransactionRequest $scheduledTransactionId): array
	{
		return $this->request('DELETE', "/plans/{$planId}/scheduled_transactions/{$this->resolveModelId($scheduledTransactionId)}", [], null);
	}

	/**
	 * @param array<string,mixed>|CreateAccountRequest $payload
	 * @return array<string,mixed>
	 */
	public function createAccount(string $planId, array|CreateAccountRequest $payload): array
	{
		return $this->request('POST', "/plans/{$planId}/accounts", [], $this->payloadToArray($payload));
	}

	/**
	 * @param array<string,mixed>|CreateCategoryRequest $payload
	 * @return array<string,mixed>
	 */
	public function createCategory(string $planId, array|CreateCategoryRequest $payload): array
	{
		return $this->request('POST', "/plans/{$planId}/categories", [], $this->payloadToArray($payload));
	}

	/**
	 * @param UpdateCategoryRequest|string $categoryId
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function updateCategory(string $planId, UpdateCategoryRequest|string $categoryId, ?array $payload = null): array
	{
		return $this->mutate('PATCH', "/plans/{$planId}/categories", 'updateCategory', $categoryId, $payload);
	}

	/**
	 * @param UpdateMonthCategoryRequest|string $categoryId
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function updateMonthCategory(string $planId, string $month, UpdateMonthCategoryRequest|string $categoryId, ?array $payload = null): array
	{
		return $this->mutate('PATCH', "/plans/{$planId}/months/{$month}/categories", 'updateMonthCategory', $categoryId, $payload);
	}

	/**
	 * @param array<string,mixed>|CreateCategoryGroupRequest $payload
	 * @return array<string,mixed>
	 */
	public function createCategoryGroup(string $planId, array|CreateCategoryGroupRequest $payload): array
	{
		return $this->request('POST', "/plans/{$planId}/category_groups", [], $this->payloadToArray($payload));
	}

	/**
	 * @param UpdateCategoryGroupRequest|string $categoryGroupId
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function updateCategoryGroup(string $planId, UpdateCategoryGroupRequest|string $categoryGroupId, ?array $payload = null): array
	{
		return $this->mutate('PATCH', "/plans/{$planId}/category_groups", 'updateCategoryGroup', $categoryGroupId, $payload);
	}

	/**
	 * @param UpdatePayeeRequest|string $payeeId
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	public function updatePayee(string $planId, UpdatePayeeRequest|string $payeeId, ?array $payload = null): array
	{
		return $this->mutate('PATCH', "/plans/{$planId}/payees", 'updatePayee', $payeeId, $payload);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function get(string $path, array $query = []): array
	{
		return $this->request('GET', $path, $query, null);
	}

	/**
	 * @param array<string,mixed>|RequestModel $payload
	 * @return array<string,mixed>
	 */
	private function payloadToArray(array|RequestModel $payload): array
	{
		return is_array($payload) ? $payload : $payload->toArray();
	}

	/**
	 * Validate one or more URL path segment values.
	 * Throws if any value is empty or contains characters that would alter the URL path or query string.
	 *
	 * @param array<string,string> $segments  Map of parameter name => value
	 * @throws YnabException
	 */
	private function validatePathSegments(array $segments): void
	{
		foreach ($segments as $name => $value) {
			if (trim($value) === '') {
				throw new YnabException("The '{$name}' parameter cannot be empty.");
			}

			if (strpbrk($value, '/?#\\') !== false) {
				throw new YnabException("The '{$name}' parameter contains invalid URL characters.");
			}
		}
	}

	/**
	 * @param RequestModel|string $idOrModel
	 * @param null|array<string,mixed> $payload
	 * @return array<string,mixed>
	 */
	private function mutate(string $httpMethod, string $basePath, string $callerName, RequestModel|string $idOrModel, ?array $payload): array
	{
		if ($idOrModel instanceof RequestModel) {
			if ($payload !== null) {
				$shortName = basename(str_replace('\\', '/', $idOrModel::class));
				throw new YnabException("When passing {$shortName}, omit the \$payload argument.");
			}

			$id = $this->resolveModelId($idOrModel);

			return $this->request($httpMethod, "{$basePath}/{$id}", [], $idOrModel->toArray());
		}

		if ($payload === null) {
			throw new YnabException("{$callerName}() requires \$payload when the ID is a string.");
		}

		$this->validatePathSegments(['id' => $idOrModel]);

		return $this->request($httpMethod, "{$basePath}/{$idOrModel}", [], $payload);
	}

	/**
	 * @param object{id:string}|string $idCarrier
	 */
	private function resolveModelId(string|object $idCarrier): string
	{
		$id = is_string($idCarrier) ? $idCarrier : ($idCarrier->id ?? null);
		if (!is_string($id)) {
			throw new YnabException('Expected a string ID or model instance with a string $id property.');
		}

		$id = trim($id);
		if ($id === '') {
			throw new YnabException('Model ID cannot be empty.');
		}

		$this->validatePathSegments(['id' => $id]);

		return $id;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function request(string $method, string $path, array $query, ?array $json): array
	{
		$base = rtrim($this->config->baseUrl, '/');
		$route = ltrim($path, '/');
		$url = "{$base}/{$route}";
		$url = $this->appendQuery($url, $query);

		$headers = $this->auth->apply([
			'Accept' => 'application/json',
		]);
		$body = null;

		if ($json !== null) {
			$headers['Content-Type'] = 'application/json';
			$body = json_encode($json, JSON_THROW_ON_ERROR);
		}

		$attempts = 0;

		do {
			$attempts++;
			$response = $this->requestSender->sendRequest(new Request($method, $url, $headers, $body));

			if ($response->getStatusCode() === 401 && $this->auth instanceof OAuthTokenAuth && $attempts === 1) {
				$newToken = $this->auth->rotateToken();
				if ($newToken !== null) {
					$headers = $this->auth->apply(['Accept' => 'application/json']);
					if ($json !== null) {
						$headers['Content-Type'] = 'application/json';
					}
					continue;
				}
			}

			if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
				$decoded = json_decode((string) $response->getBody(), true);
				if (!is_array($decoded['data'] ?? null)) {
					throw new YnabException('Unexpected response format from YNAB API.');
				}

				return $decoded['data'];
			}

			throw $this->apiException($method, $path, $response);
		} while ($attempts <= max(1, $this->config->maxRetries));

		throw new YnabException('YNAB request failed after retry attempts.');
	}

	private function apiException(string $method, string $path, ResponseInterface $response): YnabApiException
	{
		$error = YnabErrorParser::parse((string) $response->getBody());

		$retryAfter = null;
		$retryAfterHeader = $response->getHeaderLine('retry-after');
		if ($retryAfterHeader !== '' && is_numeric($retryAfterHeader)) {
			$retryAfter = (int) $retryAfterHeader;
		}

		$identifier = implode(' ', array_values(array_filter([
			$error['id'],
			$error['name'],
		], static fn (?string $v): bool => $v !== null && $v !== '')));

		$idSuffix = $identifier !== '' ? " {$identifier}" : '';
		$detailSuffix = $error['detail'] !== null ? " {$error['detail']}" : '';

		return new YnabApiException(
			message: "YNAB API request failed ({$method} {$path}, HTTP {$response->getStatusCode()}).{$idSuffix}{$detailSuffix}",
			statusCode: $response->getStatusCode(),
			retryAfterSeconds: $retryAfter,
			errorId: $error['id'],
			errorName: $error['name'],
			errorDetail: $error['detail'],
		);
	}

	/**
	 * @template T
	 * @param callable(array<string,mixed>):?T $mapper
	 * @param non-empty-string|list<non-empty-string> $key
	 * @return ResourceCollection<T>
	 */
	private function collection(string $path, array $query, string|array $key, callable $mapper): ResourceCollection
	{
		$items = [];
		$serverKnowledge = null;
		$nextQuery = $query;
		$keys = is_array($key) ? $key : [$key];
		$pageCount = 0;

		while (true) {
			$pageCount++;
			if ($pageCount > $this->config->maxPages) {
				throw new YnabException("Pagination limit of {$this->config->maxPages} pages exceeded.");
			}

			$data = $this->get($path, $nextQuery);
			$serverKnowledge = $this->serverKnowledge($data) ?? $serverKnowledge;

			$rows = $this->firstArrayByKeys($data, $keys) ?? [];
			foreach ($rows as $row) {
				if (!is_array($row)) {
					continue;
				}
				$mapped = $mapper($row);
				if ($mapped !== null) {
					$items[] = $mapped;
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

	/**
	 * @template T
	 * @param callable(array<string,mixed>):?T $mapper
	 * @param non-empty-string|list<non-empty-string> $key
	 * @return ?T
	 */
	private function item(string $path, array $query, string|array $key, callable $mapper): mixed
	{
		$data = $this->get($path, $query);
		$keys = is_array($key) ? $key : [$key];
		$row = $this->firstArrayByKeys($data, $keys);
		if (!is_array($row)) {
			return null;
		}

		return $mapper($row);
	}

	/**
	 * @param array<string,mixed> $data
	 * @param list<non-empty-string> $keys
	 * @return null|array<mixed>
	 */
	private function firstArrayByKeys(array $data, array $keys): ?array
	{
		foreach ($keys as $key) {
			$value = $data[$key] ?? null;
			if (is_array($value)) {
				return $value;
			}
		}

		return null;
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

	/**
	 * @param array<string,mixed> $data
	 * @return array<string,string>|null
	 */
	private function nextPageQuery(array $data): ?array
	{
		$nextPage = $data['next_page'] ?? null;
		if (is_int($nextPage) || is_string($nextPage)) {
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
		$normalized = [];
		foreach ($parsed as $key => $value) {
			if (!is_string($key) || !is_scalar($value)) {
				continue;
			}
			$normalized[$key] = (string) $value;
		}

		return $normalized;
	}

	/** @param array<string,scalar|null> $query */
	private function appendQuery(string $url, array $query): string
	{
		if ($query === []) {
			return $url;
		}

		$encoded = http_build_query($query);
		if ($encoded === '') {
			return $url;
		}

		$separator = str_contains($url, '?') ? '&' : '?';
		return "{$url}{$separator}{$encoded}";
	}
}
