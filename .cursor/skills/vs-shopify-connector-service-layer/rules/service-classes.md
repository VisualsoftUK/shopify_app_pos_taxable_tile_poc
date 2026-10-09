# Service Classes

## Service creation trigger (required)

Create or extend a service class when you introduce a new GraphQL workflow. Do not start with raw `api()->graph()` in consuming app code.

## Boundaries

- **Services** — orchestration, retries/polling, multi-step API interactions.
- **Actions** — thin command-style wrappers (`::for($shop)->execute(...)`) delegating to services/jobs.
- **Jobs** — async execution with explicit domain/shop context.
- **Concerns** — micro reusable behavior; not orchestration.

## Service catalog

- `ShopifyGraphQlService` — fluent GraphQL calls, retry, cost/throttle logging.
- `ShopifyFileService` — file create + readiness polling flow.
- `MerchantBracketService` — bracket assignment and queue routing logic.
- `ShopResolver` — domain-based `VsShopModel` lookup.
- `UserSettingService` — user setting retrieval and metafield-backed behavior.

## Decision rule for GraphQL calls

1. If an existing service already covers the use case, reuse it.
2. If a new reusable GraphQL flow is needed, add a service class.
3. Use object/static helper GraphQL paths only when extending existing package object workflows.

## Related skills

- `vs-shopify-connector-shop-setup` — shop context and extension points
- `vs-shopify-connector-testing-integration` — testing service workflows
