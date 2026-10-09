# Error Handling

## Primary failure sources in this package

- Shopify GraphQL errors and `userErrors` payloads.
- Shop resolution failures in queued jobs (`BaseJob`, `ShopResolver`).
- Webhook/auth signature validation failures.
- Bracket throttling misconfiguration (`BracketThrottleMiddleware`).

## Preferred handling patterns

- Use package exception types where provided (`ApiException`, domain-specific exceptions).
- Normalize GraphQL `userErrors` before branching logic.
- Keep retry logic near package API calls (service/job level), not scattered in controllers.
- Log contextual keys: shop domain, GID/topic, mutation/action name, attempt count.

## Failure boundaries

- Service-level failures should return typed/expected app-facing outcomes.
- Jobs should fail loudly on invalid package configuration and malformed context.
- Avoid swallowing package exceptions without attaching shop/action context.

## Related code anchors

- `Services\ShopifyGraphQlService`
- `Services\ShopifyFileService`
- `Jobs\HandleResourceTaggingBaseJob`
- `Jobs\Middleware\BracketThrottleMiddleware`

## Related skills

- `vs-shopify-connector-service-layer` — GraphQL service retry configuration
- `vs-shopify-connector-async-jobs` — bracket throttling details
- `vs-shopify-connector-testing-integration` — testing error paths
