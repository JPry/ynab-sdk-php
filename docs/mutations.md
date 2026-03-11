# Mutations and Request Models

Mutating methods accept typed request models from `JPry\\YNAB\\Model\\Mutation\\*`.
Legacy array and string-id signatures remain supported.

## Create a transaction

```php
use JPry\YNAB\Model\Mutation\CreateTransactionsRequest;
use JPry\YNAB\Model\Mutation\TransactionPayload;

$result = $client->createTransactions(
    'plan-id',
    CreateTransactionsRequest::single(
        new TransactionPayload(
            accountId: 'account-id',
            date: '2026-03-11',
            amount: -125000,
            payeeName: 'Grocery Store',
            memo: 'Weekly groceries',
        ),
    ),
);
```

## Update and delete by model `id`

For update/delete endpoints with an `id` in the path, the client can use the model request object's `id`.

```php
use JPry\YNAB\Model\Mutation\TransactionPayload;
use JPry\YNAB\Model\Mutation\UpdateTransactionRequest;

$update = new UpdateTransactionRequest(
    id: 'transaction-id',
    transaction: new TransactionPayload(memo: 'Corrected memo'),
);

$client->updateTransaction('plan-id', $update);
$client->deleteTransaction('plan-id', $update); // id comes from $update->id
```

## Batch patch transactions

```php
use JPry\YNAB\Model\Mutation\PatchTransactionPayload;
use JPry\YNAB\Model\Mutation\PatchTransactionsRequest;
use JPry\YNAB\Model\Mutation\TransactionPayload;

$client->patchTransactions('plan-id', new PatchTransactionsRequest([
    new PatchTransactionPayload(
        id: 'txn-1',
        transaction: new TransactionPayload(memo: 'Patched note'),
    ),
]));
```

## Other common mutations

```php
use JPry\YNAB\Model\Mutation\CreateAccountRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\CreateCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdateCategoryGroupRequest;
use JPry\YNAB\Model\Mutation\UpdateCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdateMonthCategoryRequest;
use JPry\YNAB\Model\Mutation\UpdatePayeeRequest;

$client->createAccount('plan-id', new CreateAccountRequest('Emergency Fund', 'savings', 0));
$client->createCategoryGroup('plan-id', new CreateCategoryGroupRequest('Travel'));
$client->createCategory('plan-id', new CreateCategoryRequest('Flights', 'group-id'));

$client->updateCategory('plan-id', new UpdateCategoryRequest(id: 'category-id', name: 'Airfare'));
$client->updateMonthCategory('plan-id', '2026-03-01', new UpdateMonthCategoryRequest(id: 'category-id', budgeted: 50000));
$client->updateCategoryGroup('plan-id', new UpdateCategoryGroupRequest(id: 'group-id', name: 'Trips'));
$client->updatePayee('plan-id', new UpdatePayeeRequest(id: 'payee-id', name: 'Renamed Payee'));
```

## Legacy call style (still supported)

```php
$client->updateTransaction('plan-id', 'transaction-id', [
    'transaction' => ['memo' => 'Legacy payload'],
]);
```
