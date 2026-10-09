# Queue Jobs and Webhooks

## Package job patterns

- `Jobs\BaseJob` — for domain-based shop validation and resolution.
- `Jobs\ShopifyWebhooks\BaseShopifyJob` — for webhook payload jobs (`domain`, `stdClass data`).
- Resource tagging jobs inherit `HandleResourceTaggingBaseJob` and use `ShopifyGraphQlService`.

## Webhook flow conventions

1. Verify request via package middleware (`VerifyShopify` / endpoint-specific auth middleware).
2. Parse topic/domain/payload.
3. Dispatch a lightweight job quickly — keep the HTTP handler thin.
4. Perform Shopify API work inside service/job layers with retries and structured logging.

## Queue + bracket behavior

- Dispatch may be rerouted by `BracketAwareDispatcher` based on the shop's current bracket.
- `BracketThrottleMiddleware` enforces per-bracket concurrency and requires jobs to define `maxExceptions`.

## Related rule files

- `rules/brackets-throttling.md` — bracket config and throttle middleware details
- `rules/flow-actions.md` — Shopify Flow-specific job patterns
