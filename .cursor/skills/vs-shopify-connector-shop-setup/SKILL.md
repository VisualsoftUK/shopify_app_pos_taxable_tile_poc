---
name: vs-shopify-connector-shop-setup
description: Set up shop context, configuration scoping, model contracts, and extension points in a consuming app. Use when wiring up the shop model, configuring scoping, customising package behavior, or working with shop-scoped models.
---

# VS Shopify Connector Shop Setup

> Always read the relevant sub-file before working in this area.

## Quick reference

| Concept | Class / Config |
|---|---|
| Shop contract | `Contracts\VsShopModel` |
| Shop resolver | `Services\ShopResolver` / `Contracts\ShopResolverInterface` |
| Request context | `CurrentAppInstallation::user()` |
| Shop scoping middleware | `Http\Middleware\ApplyShopScoping` |
| Shop scoped contract | `Contracts\ShopScoped` |
| Shop scope concern | `Concerns\HasShopScope` |
| Package configs | `vs-shopify-connector`, `vs-shop-scoping`, `vs-brackets`, `vs-shopify-graphql-defaults`, `shopify-flow`, `vs-shopify` |

## Rule files

### 1. Shop context → `rules/shop-context.md`

- Canonical paths for resolving shop identity (request, service, job)
- Common failure modes

### 2. Configuration and scoping → `rules/configuration-scoping.md`

- Package config surfaces and override approach
- Shop scoping middleware, contracts, and common mistakes

### 3. Extension points → `rules/extension-points.md`

- Safe customisation via contracts and bindings
- High-risk changes to avoid

### 4. Models and relationships → `rules/models-relationships.md`

- `VsShopModel` contract requirements
- App model guidance for shop-scoped models
