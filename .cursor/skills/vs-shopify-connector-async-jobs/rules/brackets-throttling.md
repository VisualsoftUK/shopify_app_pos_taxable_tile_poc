# Brackets and Throttling

## Package components

- `Services\MerchantBracketService` — bracket assignment and queue name mapping.
- `Jobs\AssignShopBracketJob` — per-shop GraphQL plan lookup and `auto_assigned_bracket` save. Dispatched by `shopify:assign-shop-brackets`; the command must not assign brackets in-process.
- `Bus\BracketAwareDispatcher` — dispatch-time queue rerouting based on shop bracket.
- `Jobs\Middleware\BracketThrottleMiddleware` — per-bracket concurrency locks.
- Config: `vs-brackets`, `vs-shopify-connector.assign_shop_brackets`.

## Job requirements

- Jobs using throttle middleware must define a valid `maxExceptions` value.
- The shop domain must be available for resolver lookup at dispatch time.
- The cache driver must support locking in non-testing environments.

## Operational guidance

- Keep bracket queue names explicit in `vs-brackets` config.
- Tune `job_throttle_levels`, `release_delay`, and `lock_ttl` based on real throughput metrics.
- Add tests for bracket-specific queue selection and middleware release behavior.

## Related rule files

- `rules/queue-jobs-webhooks.md` — general job base classes and webhook patterns
