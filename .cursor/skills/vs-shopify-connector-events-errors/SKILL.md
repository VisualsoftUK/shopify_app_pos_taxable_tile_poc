---
name: vs-shopify-connector-events-errors
description: Handle VS Shopify Connector exceptions and userErrors, and consume package domain events in app code. Use when writing listeners for connector events, handling GraphQL errors, or managing retry boundaries.
---

# VS Shopify Connector Events and Errors

> Always read the relevant sub-file before working in this area.

## Quick reference

| Concept | Class |
|---|---|
| Bracket events | `BracketAutomaticallyAssigned`, `BracketManuallyAssigned`, `BracketManualAssignmentReset` |
| Event source | `Services\MerchantBracketService` |
| GraphQL exceptions | `ApiException`, domain-specific exceptions |
| Key failure sources | `ShopifyGraphQlService`, `BaseJob`, `ShopResolver`, `BracketThrottleMiddleware` |

## Rule files

### 1. Events and listeners → `rules/events-listeners.md`

- Package events available for app code to consume
- Listener conventions and integration pattern

### 2. Error handling → `rules/error-handling.md`

- Primary failure sources and preferred handling patterns
- Logging guidance and failure boundaries
