---
name: vs-shopify-connector-performance-optimization
description: Optimize connector-specific Shopify GraphQL throughput, throttling, and queue routing behavior.
---

# VS Shopify Connector Performance Optimization

Use this skill for package-specific performance levers.

## High-impact levers in this package

- `ShopifyGraphQlService` retry/backoff controls (`maxRetries`, `retryDelay`, config defaults).
- GraphQL cost/throttle visibility logged by service methods.
- Bracket-aware queue routing (`BracketAwareDispatcher`) and bracket throttle middleware.
- Deferred dispatch patterns via `Support\\DeferredJobBatch` where applicable.

## Practical optimization approach

- Start with query/mutation call frequency and cost from package logs.
- Tune `vs-shopify-graphql-defaults` before custom retry loops.
- Use bracket queue entitlement config (`vs-brackets`) for fair throughput by merchant tier.
- Keep webhook handlers quick; offload heavy work to jobs.

## Avoid

- Copy-pasting generic Laravel cache/database advice into connector skills.
- Disabling retry/throttle protections without measurable reason.
