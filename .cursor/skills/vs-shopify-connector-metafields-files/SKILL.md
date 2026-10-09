---
name: vs-shopify-connector-metafields-files
description: Work with connector-supported metafields and Shopify file workflows using package abstractions.
---

# VS Shopify Connector Metafields and Files

Use this skill for package-supported metafield + file flows.

## Core package primitives

- `Objects\\AppInstallation` metafield helpers
- `Support\\Metafields` / `Objects\\Metafields`
- `Services\\ShopifyFileService` for staged upload publication and readiness polling
- Data object contracts under `DataObjects\\Contracts`

## How to approach changes

- Keep GraphQL interaction behind package services/objects.
- Use explicit shop context (`CurrentAppInstallation` or passed `VsShopModel`).
- Validate Shopify `userErrors` and file status transitions (`READY`, `FAILED`).

## File flow expectation

1. Stage file externally.
2. Create Shopify file via GraphQL (`fileCreate`).
3. Poll until ready via package service.
4. Persist returned Shopify URL/ID in your app domain where needed.
