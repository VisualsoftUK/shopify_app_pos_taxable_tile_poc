# Extension Points

## Primary extension seams

- `Contracts\ShopResolverInterface` (bound to `Services\ShopResolver` by default)
- `Contracts\VsShopModel` behavior on your app shop/user model
- Config-driven behavior in `vs-brackets`, `vs-shop-scoping`, `vs-shopify-graphql-defaults`
- Middleware aliases and command/config registration in `ShopifyServiceProvider`

## Safe customisation pattern

1. Prefer implementing/rebinding contracts over editing package classes.
2. Keep app-specific policy in app services, listeners, and jobs.
3. Preserve package assumptions: `shop domain` stored in `name`, valid `VsShopModel` methods intact.

## High-risk changes

- Replacing shop resolution without honoring `resolve(string $domain): ?VsShopModel`.
- Changing auth user model structure without aligning `ShopResolver` and `BaseJob` expectations.
- Overriding provider aliases/bindings without integration tests covering the change.
