<laravel-boost-guidelines>
=== .ai/architecture rules ===

## Architecture

This application is built with a Domain-Driven Design (DDD) approach, using Laravel on the backend and a React frontend. As a Shopify App, the architecture has multiple integration points and a clear separation of concerns.

### Backend (Laravel/PHP)

The backend structure contains layered components:

- Application Layer: Manages HTTP requests, controllers, middleware, and console commands.

- Domain Layer: This layer holds the core business logic, models, actions, data objects, and domain services.

- Framework Layer: Provides shared services like providers and exception handling.

### Frontend (React/JavaScript)

The frontend has both merchant and customer-facing components:

- Admin Dashboard: A React interface styled with Shopify Polaris for merchants.

- Theme Extensions:

    - Main Extension: Customer-facing theme app extension blocks built with Liquid templates and JavaScript (see `extensions/main/`).

=== .ai/laravel/12/core rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is **perfectly fine** and recommended by Laravel. Follow the existing structure from Laravel 10. We do not to need migrate to the new Laravel structure unless the user explicitly requests that.

### Laravel 10 Structure

- Middleware typically lives in `app/Application/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Application/Http/Kernel.php`
    - Exception handling is in `app/Framework/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Application/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Application/Http/Kernel.php`

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== kyon147/laravel-shopify/core rules ===

## kyon147/laravel-shopify (host app)

- This Composer package adds **Shopify embedded / custom app** support to **your** Laravel app: OAuth, session handling, billing helpers, webhooks, and related tooling. After install: `php artisan vendor:publish --tag=shopify-config`, set documented `SHOPIFY_*` keys in `.env`, run migrations, and wire **your** shop/user model to the package contract + trait.
- **Agents:** use **published** `config/shopify-app.php` and documented env keys only. Do not weaken HMAC, session token, or webhook verification in app code to “fix” auth.
- **Where to work:** **your** `.env`, `config/shopify-app.php`, `routes/*.php`, models, and middleware registration. Package source under `vendor/kyon147/laravel-shopify` is **read-only reference** (e.g. default routes in `src/resources/routes/`, middleware in `src/Http/`).

### Skill routing (activate the matching skill)

| Task in the **host app** | Skill |
| --- | --- |
| OAuth, embedded auth, `verify.shopify`, session token / App Bridge, protecting routes | `shopify-app-authentication` |
| Subscriptions, charges, `billable` middleware, plan URLs | `shopify-app-billing` |
| Webhook topics, HMAC verification, uninstall / compliance jobs, queues | `shopify-app-webhooks` |
| PHPUnit / Pest / HTTP tests for **your** app using the package | `shopify-app-integration-testing` |
| Expiring offline tokens, refresh skew, migrations, `APP_KEY`, refresh failures | `shopify-app-offline-access-tokens` |

=== visualsoft/vs-mantle/core rules ===

## VS-Mantle Integration

- Your `User` model must implement `Visualsoft\Mantle\Contracts\MantleableModel` and use the `Visualsoft\Mantle\Concerns\MantleModel` trait.
- Set `MANTLE_APP_ID` and `MANTLE_API_KEY` environment variables. Point `vs-mantle.user_model` at your User class.
- Publish migrations with `php artisan vendor:publish --tag=visualsoft-assets-migrations`, then run `php artisan migrate`.
- Never update `mantles`, `mantle_plans`, or `mantle_features` tables directly — all plan/subscription state arrives via Mantle webhooks.
- Check feature access with `$user->canUseFeature('key')`, `$user->isFeatureEnabled('key')`, or Laravel Pennant `Feature::active('feature_key', $user)`.
- Three usage metrics approaches: adhoc (`AdhocUsageMetricService`), aggregated (`BaseAggregation` subclasses), and event-driven (`MantleEventDrivenUsageMetrics` trait on your models).
- Ensure a queue worker is running for the `mantle_usage_metrics` queue (configurable via `vs-mantle.usage_metrics.queue_name`).
- Ensure Laravel's scheduler is running — the package registers scheduled commands for syncing, aggregations, and pruning.
- Routes are auto-registered: `app/mantle/plans` (billing page), `mantle/webhook/{type?}` (webhook ingestion), and others.

### Skills

- Activate `vs-mantle` when working with Mantle plans, subscriptions, usage metrics, or feature flags.

=== visualsoft/vs-shopify-admin/core rules ===

## VS Shopify Admin

- Routes auto-register under `api/admin`; the app must have either an `admin` or `verify.shopify` middleware group.
- Publish and run migrations before use: `php artisan vendor:publish --tag=visualsoft-assets-migrations && php artisan migrate`.
- `VS_API_SECRET` is required. All other env vars (`CROSS_SELLING_*`, `APP_REVIEW_*`, `VS_ADMIN_*`) are optional with defaults.
- `BEACONS_ENABLED=true` is required for cross-sell click processing. Ensure the `cross_selling_clicks` Redis list is accessible.
- Laravel's scheduler must run: `ProcessClicks` is scheduled via cron from `vs-admin-cross-selling.process_cross_sell_clicks_cron_expression`.
- Config keys: `vs-admin-cross-selling`, `vs-admin-services`, `vs-admin-app-review`, `vs-admin-tables` — merged automatically, never published.
- Register Vite aliases in `vite.config.js`: `@admin` → package `resources/js/src`, `@admin-css` → package `resources/css`, `@admin-locales` → package `locales`, `@mantle-locales` → `vendor/visualsoft/vs-mantle/locales`, `@app-pricing-locales` → `vendor/visualsoft/vs-shopify-app-pricing/locales`.
- ⌘/Ctrl/middle-click on `/app` links is rewritten to Admin embed URLs via App Bridge `shopify.config` (shop + apiKey); no consumer config required.
- The `SetUpController` redirects to the named route `home` after saving — the consuming app must define this route.
- Custom app-review checks must implement `Visualsoft\ShopifyAdmin\Contracts\ReviewCheck` and be registered in `vs-admin-app-review.checks`.

### Skills

- Activate `vs-shopify-admin-integration` when integrating any feature from this package.

=== visualsoft/vs-shopify-beacons/core rules ===

## Shopify Beacons

- Publish the migration before migrating: `php artisan vendor:publish --tag=shopify-beacons-assets-migrations`.
- Add `config/beacons.php` in the consuming app and populate `handlers` — mapping `beaconType` strings to handler classes that implement `BeaconHandlerInterface`.
- Routes (`POST /proxy/beacon`, `POST /checkout-pixel/beacon`, `POST /api/admin/beacon`) are registered automatically. The middleware names `auth.proxy`, `auth.checkout_pixel`, and `verify.shopify` (or `admin`) must exist on the router.
- Failures are persisted to `beacon_errors`. Scheduling runs only when `BEACONS_ENABLED=true`.

### Skills

- Activate `vs-shopify-beacons-integration` when installing the package, implementing handlers, configuring scheduling, or testing beacon flows.

=== visualsoft/vs-shopify-connector/core rules ===

## VS Shopify Connector

Internal package for shared Shopify integration patterns across Visualsoft apps.

### Core usage rules

- Prefer package service abstractions over raw Shopify API calls.
- For new GraphQL workflows in consuming apps, create or extend a service class first.
- Prefer `ShopifyGraphQlService::for(...)->withQuery(...)->withParams(...)->get()` over direct `api()->graph()` in consuming app code.
- Use package contracts and middleware seams before overriding internals.

### Existing service classes

- `ShopifyGraphQlService` - fluent GraphQL wrapper with retry and throttle-aware behavior.
- `ShopifyFileService` - staged upload publication and file readiness polling.
- `MerchantBracketService` - bracket assignment and queue entitlement mapping.
- `ShopResolver` - domain to `VsShopModel` resolution via configured auth model.
- `UserSettingService` - package user settings and metafield-backed retrieval flow.
- `ShopPrimaryDomainService` - fetch and persist Shopify `shop.primaryDomain.host`; used by lazy reads and the weekly sync job.

### How we structure package-facing app code

- **Service** for reusable orchestration and GraphQL work.
- **Action** for thin app-facing commands that delegate.
- **Job** for asynchronous execution with explicit domain/shop context.
- **Concern** for small reusable behavior slices.

### Testing expectations for service workflows

- Mock GraphQL/service chains deterministically; avoid live Shopify calls.
- Reuse package-style test setup patterns (`tests/TestCase.php`, `tests/Pest.php`).
- Add regression tests for service behavior changes (retry, throttling, context resolution).

### Package-first references

- `src/Services/`
- `src/Contracts/`
- `src/Jobs/`
- `src/Http/Middleware/`
- `config/vs-shopify-graphql-defaults.php`
- `config/vs-brackets.php`
- `config/vs-shopify-connector.php` → `sync_primary_domain` (cron + overlap window for `shopify:sync-primary-domains`)

### Shop primary domain (consuming apps)

- Publish package migrations so the shop user table has a `primary_domain` column; the package ships a migration you can publish alongside existing connector migrations.
- The scheduled sync command queries `where('active', 1)` on `auth.providers.users.model` — that column must exist and `1` must mean “include this shop in sync” (inactive shops are skipped).
- Weekly (or custom cron) the package runs `shopify:sync-primary-domains`, which dispatches a job per eligible shop: backfills when `primary_domain` is empty, otherwise refreshes from Shopify for custom-domain changes.
- Internal monitoring shop `name` values are excluded by the package; no app configuration for that.

### Skills

Activate the relevant skill when working in that area:

- `vs-shopify-connector-service-layer` — GraphQL service calls, service/action/job boundaries, service catalog.
- `vs-shopify-connector-async-jobs` — webhook handlers, queued jobs, Shopify Flow actions, bracket throttling.
- `vs-shopify-connector-shop-setup` — shop context resolution, config scoping, model contracts, extension points.
- `vs-shopify-connector-events-errors` — package domain events and listeners, exception and userErrors handling.
- `vs-shopify-connector-http-auth` — package middleware and Shopify auth verification flows.
- `vs-shopify-connector-metafields-files` — metafield helpers and Shopify file upload/readiness workflows.
- `vs-shopify-connector-getting-started` — first-time integration checklist and package conventions.
- `vs-shopify-connector-testing-integration` — testing service workflows with GraphQL mocks and shop context.
- `vs-shopify-connector-performance-optimization` — GraphQL throughput, throttle tuning, bracket queue routing.
- `vs-shopify-connector-primary-domain` — `primary_domain` column, `active` flag, scheduled sync command, env tuning.

=== visualsoft/vs-shopify-partitions/core rules ===

## VS Shopify Partitions: Config and Runtime

- Publish and manage package config in `config/vs-partitions.php`.
- Define partitioned tables under `tables` with `name`, `type`, `date_column`, and `prune_after`.
- Use `prune_after: null` to disable pruning for a table without removing create-future support.
- Schedule behavior is config-driven via cron and overlap keys for create/prune commands.
- Partition naming and lifecycle are package-managed; avoid ad-hoc partition SQL in application code.

### Skills

- Activate `vs-shopify-partitions-config-src` when integrating or customizing package config/runtime behavior.

=== visualsoft/vs-ai-rules/core rules ===

## Visualsoft Conventions

### Tooling

- After modifying any PHP file, run `./vendor/bin/pint --dirty` to fix formatting.
- Run tests with `./vendor/bin/pest`, not `php artisan test`.

### Codacy — Explicit Comparisons

Codacy rejects implicit boolean checks. Always write explicit comparisons:

```php
if (empty($variable) === false) {}
if ($boolean === true) {}
if ($model === null) {}
```

Keep NPath complexity below 100.

### Governance

- Before proposing architectural or product decisions, read `.github/ARCHITECTURE.md`, `.github/PRODUCT_STRATEGY.md`, and accepted `.github/adr/`.
- For when to update those files, follow the `lifecycle-governance` guideline (always compiled into AGENTS.md).

### Skills

- Activate `visualsoft-best-practices` when writing or reviewing code in any Visualsoft package or application, then read the relevant rule file for the domain in use.

=== visualsoft/vs-ai-rules/lifecycle-governance rules ===

## Lifecycle Governance

Binding sources live under `.github/`. GitHub lifecycle agents (Policy, Technical Refinement, Implementation) load those files only — they do not read `.ai/guidelines` or AGENTS.md.

- `.github/ARCHITECTURE.md` — technical ground truth: stack, paths, boundaries, non-negotiables.
- `.github/PRODUCT_STRATEGY.md` — product charter: does / does not / will never / interaction rules.
- `.github/adr/*.md` — decision records; only `status: accepted` is binding.

`.ai/guidelines` and skills are implementation helpers. They must not contradict `.github`. If they disagree, `.github/ARCHITECTURE.md` wins for binding paths and invariants. Fix code and governance in the same PR — no silent drift.

### Before finishing a PR

| If you changed… | Update… |
|-----------------|---------|
| New shipped merchant/shopper/POS job | `PRODUCT_STRATEGY.md` → In scope / does (same PR or right after ship) |
| New interaction rule or will-never | `PRODUCT_STRATEGY.md`; ADR if reversing a prior will-never |
| Issue has Policy **Charter catch-up** note | `PRODUCT_STRATEGY.md` when the surface ships |
| New route, extension, webhook, package boundary, i18n master, CI/deploy step | `ARCHITECTURE.md` (prefer same PR) |
| New technical invariant agents must not violate | `ARCHITECTURE.md` → Non-negotiables; ADR if it was a real architectural fork |
| Class/job/snippet detail only | App `.ai/guidelines` or domain skill; also `ARCHITECTURE.md` if paths/boundaries moved |

ADRs are not a changelog of ARCHITECTURE. They record decisions (context, we will/will not, trade-offs). Retroactive ADRs are fine. Routine changes: update ARCHITECTURE only.

### Factory plan on this branch

If this branch has  docs/plans/<repo>-<n>-technical-plan.md  (lifecycle Implement output), that file is the source of truth for **this PR**, not a frozen Gate B snapshot. It is not a binding  .github  source.

When approach, files, tests, i18n, or risks diverge from that plan:

1. Locate the existing plan on **this branch**. Do not create a second plan file.
2. Update the relevant sections in the **same change set** as the code. Surgical edits only — do not rewrite the whole plan.
3. Add or append  ## Deviations : what changed, why, date.
4. Do **not** silently expand product scope. If Gate A, Size, or impacted repos would change, stop and tell the developer to update the issue (and replan if implementation has not started).
5. If you followed the plan, do not touch it.

Still apply the table above when the change also moves shipped jobs, paths, or invariants.

</laravel-boost-guidelines>
