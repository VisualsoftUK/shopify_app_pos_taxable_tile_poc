---
name: vs-shopify-connector-http-auth
description: Apply VS Shopify Connector HTTP auth and verification middleware correctly.
---

# VS Shopify Connector HTTP and Auth

Use this skill for package middleware and auth flows tied to Shopify requests.

## Middleware surface

- `VerifyShopify` (optimized shop reuse vs default package behavior)
- `AuthJwt` base + variants:
  - `AuthPos`
  - `AuthCheckoutPixel`
- `AuthFlowAction` for Flow-trigger signature validation

## Expected usage

- Apply connector middleware to Shopify-facing routes.
- Resolve shop identity through validated Shopify JWT/signature inputs.
- Use package middleware before custom app middleware that assumes authenticated shop context.

## Security boundaries

- Trust Shopify headers/tokens only after package middleware verification.
- Avoid re-implementing HMAC/JWT parsing if package middleware already supports the endpoint type.

## Related package skills

- `vs-shopify-connector-flow-actions`
- `vs-shopify-connector-shop-context`
- `vs-shopify-connector-queue-jobs-webhooks`
