# Events and Listeners

## Package events to consume

- `BracketAutomaticallyAssigned`
- `BracketManuallyAssigned`
- `BracketManualAssignmentReset`

These are emitted by `MerchantBracketService` workflows and are the intended integration seam for reacting to bracket changes in consuming apps.

## Listener conventions

- Keep listeners app-specific and side-effect focused (notifications, audit, downstream sync).
- Do not mutate package internals directly from listeners.
- Keep listener logic idempotent for queue-safe execution.

## Integration pattern

1. Subscribe in your app's event service provider.
2. Read the event payload (`VsShopModel`, `MerchantBracket`) and branch on app needs.
3. Delegate heavy work to app jobs/services rather than doing it inline in the listener.
