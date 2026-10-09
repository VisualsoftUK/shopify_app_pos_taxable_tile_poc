---
name: vs-shopify-connector-getting-started
description: Start integrating VS Shopify Connector using service-first package conventions.
---

# VS Shopify Connector Getting Started

Use this skill for first-time integration of `visualsoft/vs-shopify-connector` in a consuming Laravel app.

## Use this skill when

- You are wiring this package into an app for the first time.
- You need the connector entry points and boundaries.
- You want package conventions, not generic Laravel guidance.

## Package-first integration checklist

1. Install package and run app setup (`composer install`, `php artisan package:discover`).
2. Publish package assets/config only when you need app-level overrides.
3. Ensure your shop user model satisfies `VsShopModel` expectations.
4. Confirm package configs are merged by `ShopifyServiceProvider`.

## Service-first rule

- Use service classes for GraphQL workflows.
- Prefer `ShopifyGraphQlService::for(...)->withQuery(...)->get()` over direct `api()->graph()` in consuming app code.

## Existing service inventory

- `ShopifyGraphQlService`
- `ShopifyFileService`
- `MerchantBracketService`
- `ShopResolver`
- `UserSettingService`

## Related package skills

- `vs-shopify-connector-service-classes`
- `vs-shopify-connector-graphql-service`
- `vs-shopify-connector-testing-integration`
- `vs-shopify-connector-extension-points`
- `vs-shopify-connector-shop-context`
