---
name: vs-shopify-connector-service-layer
description: Use VS Shopify Connector service classes and GraphQL service as the default Shopify API path. Use when writing new GraphQL workflows, calling Shopify APIs, or adding service/action classes in consuming apps.
---

# VS Shopify Connector Service Layer

> Always read the relevant sub-file before working in this area.

## Quick reference

| Concept | Class |
|---|---|
| GraphQL wrapper | `ShopifyGraphQlService` |
| File workflows | `ShopifyFileService` |
| Bracket/queue | `MerchantBracketService` |
| Shop resolution | `ShopResolver` |
| User settings | `UserSettingService` |

## Rule files

### 1. GraphQL service → `rules/graphql-service.md`

- Default fluent call pattern (`for()->withQuery()->get()`)
- Retry and error expectations
- When to use existing services vs creating new ones

### 2. Service classes → `rules/service-classes.md`

- Service creation trigger and boundaries (Service / Action / Job / Concern)
- Service catalog and decision rule for GraphQL calls
