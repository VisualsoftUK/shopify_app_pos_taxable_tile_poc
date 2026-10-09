---
name: vs-shopify-connector-testing-integration
description: Test VS Shopify Connector service-first integrations using package-style GraphQL mocks and shop-context setup.
---

# VS Shopify Connector Testing Integration

Use this skill for connector-specific test strategy in service-heavy integrations.

## Package testing anchors

- `tests/TestCase.php` (provider wiring + testbench environment)
- `tests/Pest.php` (helpers and shared setup)
- `tests/Unit/Services/ShopifyGraphQlServiceTest.php`
- `tests/Unit/Services/ShopifyFileService*Test.php`
- Flow path tests under `tests/Unit/Http/*Flow*Test.php`

## Service testing pattern

- Mock service GraphQL chain (`for()->withQuery()->withParams()->get()`) deterministically.
- Mock `CurrentAppInstallation::user()` when service resolves request-scoped shop context.
- Assert retry/throttle behavior and `userErrors` handling, not just happy path payloads.

## What to cover for new service workflows

- Correct shop context propagation (`VsShopModel`/domain resolver path).
- Retry and backoff behavior from `vs-shopify-graphql-defaults`.
- Error wrapping/propagation contract.
- Interactions with queue/webhook middleware when applicable.

## Related package skills

- `vs-shopify-connector-service-classes`
- `vs-shopify-connector-graphql-service`
- `vs-shopify-connector-shop-context`
- `vs-shopify-connector-error-handling`
