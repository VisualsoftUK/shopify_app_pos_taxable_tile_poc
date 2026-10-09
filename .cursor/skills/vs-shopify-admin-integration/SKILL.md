---
name: vs-shopify-admin-integration
description: Consumer knowledge for integrating vs-shopify-admin features into a Shopify Laravel app. Use when working with cross-selling, app review, setup guide, i18n, paginated tables (PHP GetList), or Polaris IndexTable/Filters with Inertia partial reload (TableUtils).
---

# VS Shopify Admin — Consumer Integration

## Quick reference

| Feature | Key class / component |
|---|---|
| Cross-selling apps | `CrossSellingService`, `CrossSellingApp.jsx`, `CrossSellingCard.jsx` |
| App review prompt | `ShouldShowAppReview`, `ReviewCheck`, `RequestAppReview.jsx` |
| Setup guide | `GetSetUpSteps`, `SaveSetUpStep`, `SetupGuide.jsx` |
| i18n | `createI18nInstance`, `useLocale` (from `I18n.js`) |
| Paginated tables (PHP) | Abstract `GetList`, `PaginatedData` |
| Index table / filters (React) | `@admin/Utils/Tables/TableUtils` — `useIndexTableState`, `createPagination`, `createTableSort`, `getTableParams`, … |

## Rule files

Always read the relevant rule file before working in that area.

### 1. Cross-selling → [`rules/cross-selling.md`](rules/cross-selling.md)

- Rendering cross-sell apps, Redis click tracking pipeline, config keys

### 2. App review → [`rules/app-review.md`](rules/app-review.md)

- `ReviewCheck` interface, built-in checks, adding custom checks via config

### 3. Setup guide → [`rules/setup-guide.md`](rules/setup-guide.md)

- `SetUpStep` model, `GetSetUpSteps`/`SaveSetUpStep` actions, `SetupGuide` component

### 4. i18n → [`rules/i18n.md`](rules/i18n.md)

- `createI18nInstance`, `useLocale`, translation merge layers, Vite alias requirements

### 5. Tables (PHP) → [`rules/tables.md`](rules/tables.md)

- Extending `GetList`, allowed sorts/filters, `PaginatedData` response shape

### 6. Index tables (React) → [`rules/index-table-utils.md`](rules/index-table-utils.md)

- `TableUtils` import path, filter prop shape, `partialReload` / `only`, i18n keys for `common.*`
