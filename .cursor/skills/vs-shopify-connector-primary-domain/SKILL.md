---
name: vs-shopify-connector-primary-domain
description: Shop primary_domain persistence, weekly sync command, and app schema expectations (active flag, migrations). Use when configuring or debugging primary domain sync in consuming apps.
---

# Shop primary domain (consumer)

## Quick reference

| Concept | Detail |
| --- | --- |
| Lazy read | `HasShopifyIdentifiers::getPrimaryDomain()` / `VsShopModel::getPrimaryDomain()` — fills `primary_domain` from Shopify when empty |
| Bulk sync | Artisan `shopify:sync-primary-domains` — dispatches `RefreshShopPrimaryDomainJob` per eligible shop |
| Schedule | Package registers the command on a cron from `vs-shopify-connector.sync_primary_domain` |
| Env | `SHOPIFY_SYNC_PRIMARY_DOMAIN_CRON`, `SHOPIFY_SYNC_PRIMARY_DOMAIN_WITHOUT_OVERLAPPING_MINUTES` |

## App requirements

1. **Column `primary_domain`** on the model behind `config('auth.providers.users.model')` — publish the package migration or add an equivalent migration before relying on sync or `getPrimaryDomain()`.
2. **Column `active`** — integer/boolean column; the sync command uses `where('active', 1)`. Shops with other values are never queued for this job.
3. **Soft deletes** — if the model uses `deleted_at`, trashed rows are excluded (`whereNull('deleted_at')`).
4. **Known-invalid access tokens** — shops whose `invalid_token` matches `password` are excluded from `shopify:sync-primary-domains` and `shopify:assign-shop-brackets`. Use `User::query()->withoutKnownInvalidAccessToken()` on any other shop list that will call GraphQL.

## Operational notes

- Jobs call Shopify GraphQL per shop; scale queue workers accordingly for shop count.
- Custom primary domains in Shopify are picked up on refresh when `primary_domain` was already set; empty values are backfilled on the next run.
- Tune cron and `withoutOverlapping` via env keys above (defaults live in `config/vs-shopify-connector.php` after publish).

## Package maintainer detail

Contributor-only behaviour (command filters, service methods, tests) lives under `.ai/skills/vs-shopify-connector/rules/primary-domain.md` — activate `vs-shopify-connector` when changing the package implementation.
