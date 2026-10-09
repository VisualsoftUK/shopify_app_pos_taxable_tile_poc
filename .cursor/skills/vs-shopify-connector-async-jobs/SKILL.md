---
name: vs-shopify-connector-async-jobs
description: Implement connector-aligned webhook, queued-job, Shopify Flow, and bracket-throttling patterns. Use when writing jobs, webhook handlers, Flow actions, or bracket-aware queue behavior.
---

# VS Shopify Connector Async Jobs

> Always read the relevant sub-file before working in this area.

## Quick reference

| Concept | Class |
|---|---|
| Base job | `Jobs\BaseJob` |
| Webhook job base | `Jobs\ShopifyWebhooks\BaseShopifyJob` |
| Bracket dispatcher | `Bus\BracketAwareDispatcher` |
| Bracket throttle | `Jobs\Middleware\BracketThrottleMiddleware` |
| Flow auth middleware | `Http\Middleware\AuthFlowAction` |
| Flow controller | `Http\Controllers\FlowActionController` |
| Outbound flow job | `Jobs\TriggerShopifyFlow` |
| Config | `vs-brackets`, `shopify-flow` |

## Rule files

### 1. Queue jobs and webhooks → `rules/queue-jobs-webhooks.md`

- Job base classes and webhook flow conventions
- Queue + bracket dispatch behavior

### 2. Flow actions → `rules/flow-actions.md`

- Inbound and outbound Shopify Flow conventions
- Auth middleware and controller routing

### 3. Brackets and throttling → `rules/brackets-throttling.md`

- Bracket-aware queue routing
- `BracketThrottleMiddleware` requirements and tuning
