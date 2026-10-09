# Index tables — React (`TableUtils`)

## When to use

Use when building **merchant admin** list pages with **Shopify Polaris** `IndexTable` / `Filters`, **Inertia** partial reloads, and server-driven **sort** + **filter** query params — same pattern as multiple Visualsoft apps.

The module lives in the package at `resources/js/src/Utils/Tables/TableUtils.js`. Consumers import via the **`@admin`** Vite alias (see package `README.md`).

```js
import {
    useIndexTableState,
    useIndexPageState,
    createPagination,
    createTableSort,
    getTableParams,
    mergeFilterValue,
    createTableFilterHandlers,
    getIndexTableBaseProps,
    getIndexTableEmptyState,
    getEmptyStateMarkups,
    formatDuration,
    useFormatDuration,
} from '@admin/Utils/Tables/TableUtils';
```

## Backend contract (filters)

Helpers expect Inertia props shaped like **`BaseFilterStateData`**:

- `filters.choices` — object keyed by filter field name; each value is an array of `{ value, label }` (labels are often i18n keys).
- `filters.currentValues` — object keyed by field; each value is a `string`, `string[]`, or empty.

`getFilterControlsFromFilters`, `useIndexTableState`, and `getTableParams` assume this shape. If your app uses a flatter structure, **adapt in the page** before passing into these helpers (or extend the backend to emit `choices` + `currentValues`).

## Sort and query params

- **`getTableParams({ sort, filters })`** — builds `{ sort?, filter? }` for merging into pagination / visits. Sort uses Laravel-style strings: ascending `name`, descending `-name`.
- **`createTableSort`** / **`useIndexTableState`** — wire Polaris `IndexTable` sort callbacks to `partialReload` with `only`, merged params, and `page: 1` on sort change.

Ensure your **PHP `GetList`** (see [tables.md](tables.md)) `allowedSorts()` / `allowedFilters()` align with what the frontend sends.

## `partialReload` and `only`

`createPagination`, `createTableSort`, `createTableFilterHandlers`, and `useIndexTableState` call **`partialReload`** from `@admin/Utils/Inertia/PartialReload`. Pass the same **`only`** array you use elsewhere on that page (typically the prop key holding table rows plus `sort`, `filters`, and any shared props like `mantlePlan` if the default hook includes it).

## i18n

- **`getCommonStatusMap(translate)`** — expects `common.active`, `common.inactive`, `common.archived` (provided in package `locales/*.json` under `common`).
- **`useFormatDuration`** — expects `common.duration_suffix_year` through `common.duration_suffix_second`. Merge package locales into the app i18n bundle so these keys resolve, or override with your own strings when wrapping `formatDuration` directly.

## Empty states and Polaris

`getEmptyStateMarkups` uses Polaris **`EmptyState`**. Tests in the package **mock `@shopify/polaris`** where `TableUtils` is imported in Node (Polaris expects browser APIs such as `matchMedia`).

## Related

- PHP list actions and `PaginatedData`: [tables.md](tables.md)
- Vite aliases and i18n bootstrap: [i18n.md](i18n.md)
