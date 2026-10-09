# Flow Actions

## Package flow surface

- `Http\Middleware\AuthFlowAction` — verifies Flow HMAC and shop domain payload.
- `Http\Controllers\FlowActionController` — resolves action class from configured namespace.
- `Jobs\TriggerShopifyFlow` — sends outbound `flowTriggerReceive` mutations.
- Config: `shopify-flow.actions_namespace`.

## Inbound flow conventions

1. Protect routes with `AuthFlowAction` middleware.
2. Route segment maps kebab to Studly class name (`test-action` → `TestAction`).
3. Keep action handler execution thin; delegate heavy work to jobs/services.

## Outbound flow conventions

- Set shop context before static/object-based API usage in jobs.
- Validate and log GraphQL `userErrors` in the trigger job path.

## Related rule files

- `rules/queue-jobs-webhooks.md` — general job and webhook patterns
- See `vs-shopify-connector-http-auth` skill for middleware surface
