---
name: visualsoft-best-practices
description: Apply Visualsoft-specific coding conventions when writing or reviewing code in Visualsoft packages and applications. Supplements Laravel Boost's built-in skills with team preferences. Use when working with PHP, JavaScript, React, or writing tests, and when updating .github ARCHITECTURE, PRODUCT_STRATEGY, or ADRs — activate the relevant rule file for the domain in use.
---

# Visualsoft Best Practices

These rules override or extend the defaults provided by Laravel Boost's built-in skills. When there is a conflict, these take precedence.

Always use a sub-agent to read the relevant rule file before applying guidelines.

## Quick Reference

### 1. PHP Conventions → `rules/php-guidelines.md`

- Early returns over nested if/else
- Controller method whitelist and parameter ordering
- Action class pattern (`::for()->method()`)
- File and class suffix standards
- Data objects with `spatie/laravel-data`
- Laravel model and collection preferences

### 2. Testing Conventions → `rules/testing-guidelines.md`

- Default `UserFactory` state (`withMantlePlan`)
- Inertia deferred prop test pattern (`reloadOnly`)

### 3. JavaScript Conventions → `rules/js-guidelines.md`

- `const` by default, never `var`; `===` always; no `.then()` chains
- Template literals over concatenation; `??` over `||` for fallbacks
- `Promise.all()` for parallel async; descriptive error messages
- File naming, folder separation (`/components`, `/utils`, `/hooks`)
- JSDoc required on every function and custom hook

### 4. React Conventions → `rules/react-guidelines.md`

- Apply **in addition** to `rules/js-guidelines.md` when working in React files
- Skeleton required for every data-fetching component
- Boolean/event handler naming conventions (`isLoading`, `handleClick`, `onClick`)
- No inline styles; prop destructuring; self-closing tags
- Polaris first for Shopify apps; `Modal` from `app-bridge-react`

### 5. Governance docs → `rules/governance-docs.md`

- When to update `.github/ARCHITECTURE.md`, `PRODUCT_STRATEGY.md`, and ADRs vs app `.ai/guidelines`

## How to Apply

1. Identify the language/domain of the current task.
2. Read the relevant rule file(s) from the list above.
3. For React work, read both `js-guidelines.md` and `react-guidelines.md`.
4. When editing `.github/ARCHITECTURE.md`, `PRODUCT_STRATEGY.md`, or ADRs, read `governance-docs.md`.
5. Apply those rules alongside the corresponding Boost skill (e.g. `laravel-best-practices` for PHP, `pest-testing` for tests, `inertia-react-development` for React).
