# Migration Notes: Budgets to Plans

YNAB API `v1.79.0` documents `plans` as the primary resource naming.
This SDK supports the new plan-named endpoints and keeps budget-named API surfaces for compatibility.

## What changed in this SDK

- Requests target `/plans/{plan_id}` routes.
- Plan methods (`plans()`, `defaultPlan()`, `plan()`) are available.
- Legacy budget-named APIs remain available: `budgets()`, `defaultBudget()`, `JPry\\YNAB\\Model\\Budget`.

Budget-named APIs are deprecated and emit `E_USER_DEPRECATED` warnings.

## Endpoint parameter guidance

- Prefer using `planId` values in new code.
- Existing `budgetId` parameter names are retained where needed for backward compatibility.

## Mutation method compatibility

- Legacy payload arrays are still accepted.
- Typed request models are now supported for mutation methods.
- For update/delete methods that use an `id` path segment, you can pass the request model object directly and the SDK will use its `id`.
