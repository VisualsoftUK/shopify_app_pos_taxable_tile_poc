---
name: vs-mantle
description: Covers integrating vs-mantle into a Shopify Laravel app — User model setup, usage metrics (adhoc, aggregated, event-driven), feature flags, billing plans, configuration, and queue/scheduler requirements. Use when working with Mantle plans, subscriptions, usage metrics, feature checks, or MantleableModel.
---

# VS-Mantle (Consumer)

## Quick reference

| Concept | Class / Config |
|---|---|
| User contract | `Visualsoft\Mantle\Contracts\MantleableModel` |
| User trait | `Visualsoft\Mantle\Concerns\MantleModel` |
| Adhoc usage metrics | `Visualsoft\Mantle\Services\AdhocUsageMetricService` |
| Aggregated usage metrics | Extend `Visualsoft\Mantle\Aggregations\BaseAggregation` |
| Event-driven usage metrics | `Visualsoft\Mantle\Concerns\MantleEventDrivenUsageMetrics` trait |
| Usage metrics contract | `Visualsoft\Mantle\Contracts\ProvidesMantleEventDrivenUsageMetrics` |
| Feature usage info | `$user->canUseFeature('key')`, `$user->isFeatureEnabled('key')` |
| Pennant integration | `Feature::active('feature_key', $user)` |
| Config key | `vs-mantle` |
| Queue name | `vs-mantle.usage_metrics.queue_name` (default `mantle_usage_metrics`) |
| Mantle identify / customer token | `IdentifyService` + `PersistMantleCustomerIdentifyAction`; queued on Shopify `AppInstalledEvent` (`IdentifyMantleCustomerOnAppInstalledListener`), again on Mantle `customers/installed` (`CustomerInstalledListener`) if token still blank, and when `getMantleCustomerApiToken()` runs with empty `mantle.api_token` |

## User model setup

Your `User` model must implement `MantleableModel` (which extends `VsShopModel` from vs-shopify-connector) and use the `MantleModel` trait:

```php
use Visualsoft\Mantle\Concerns\MantleModel;
use Visualsoft\Mantle\Contracts\MantleableModel;

class User extends Model implements MantleableModel
{
    use MantleModel;
}
```

The `MantleModel` trait:
- Auto-creates a `Mantle` record when the user is retrieved (via `retrieved` event).
- Calls Mantle `POST /identify` via `PersistMantleCustomerIdentifyAction` when `getMantleCustomerApiToken()` is used and `mantle.api_token` is empty (offline token required). The same action runs queued on `AppInstalledEvent` and again on Mantle `customers/installed` after plan sync if needed.
- Provides `mantle()` HasOne relationship, `getMantleAccessToken()`, `getMantleCustomerApiToken()`, `hasActiveMantlePlan()`.
- Adds feature checking: `canUseFeature()`, `isFeatureEnabled()`, `isFeatureLimitExceeded()`, `getFeatureInfo()`.
- Adds `getPlanData()` for serialising the user's current plan + features.
- Adds `hasReachedPlanLimit()` to check if any limit-type feature is at capacity.
- Adds `scopeForMantleAccessToken()` query scope.

Point the config at your User class:

```php
// config/vs-mantle.php or .env
// vs-mantle.user_model = 'App\Models\User'
```

## Feature flags

Mantle features are automatically registered as Laravel Pennant features. When plans or features are updated via webhook, the feature cache is regenerated.

Check feature access:

```php
// Via MantleModel trait methods (recommended)
$user->canUseFeature('my_feature');       // enabled AND within limit
$user->isFeatureEnabled('my_feature');    // enabled regardless of limit
$user->isFeatureLimitExceeded('my_feature'); // true if limit-type and exceeded

// Via Laravel Pennant
Feature::active('my_feature', $user);
```

Feature types from Mantle:
- **Boolean** — simple on/off toggle per plan.
- **Limit** — integer cap; `-1` means unlimited. Tracked in `mantle_feature_usage` table.
- **Limit with overage** — soft limit where usage beyond the cap incurs extra charges.

## Usage metrics

### 1. Adhoc (one-off events)

Fire a usage event from anywhere:

```php
(new AdhocUsageMetricService)
    ->withProperties(['key' => 'value'])
    ->asEventName('my_event_name')
    ->for($shop)
    ->log('my_action');
```

### 2. Aggregated (scheduled batch reporting)

Create a class extending `BaseAggregation`:

```php
use Visualsoft\Mantle\Aggregations\BaseAggregation;

class WishlistsCreated extends BaseAggregation
{
    protected int $secondsBetweenRuns = 3600;
    protected string $apiEventName = 'wishlists_created';
    protected string $sourceDataModel = Wishlist::class;
    protected array $sourceSelectRaw = ['count(DISTINCT id) as num_wishlists'];
    protected array $sourceCriteria = ['deleted_at' => null];
    protected bool $sendWithNoResults = true;
}
```

Register it in your app's config:

```php
// config/vs-mantle.php
'usage_metrics' => [
    'aggregations' => [
        \App\Aggregations\WishlistsCreated::class,
    ],
],
```

The `mantle:run-aggregations` command (scheduled automatically) dispatches a batched `RunAggregationsForUserJob` per eligible user (see `run_aggregations_batch_size`); workers on `vs-mantle.usage_metrics.queue_name` run each aggregation.

Key extension points on `BaseAggregation`:
- `$applyUserSpecificRefinement` — set to `true` to scope the query per-user (adds `where user_id = ?`).
- `shouldReportAggregation($properties)` — return `false` to skip reporting when data is empty.
- `preProcessProperties($properties, $lastRunDate)` — transform properties before submission.

### 3. Event-driven (model lifecycle)

Have your model implement the contract and use the trait:

```php
use Visualsoft\Mantle\Concerns\MantleEventDrivenUsageMetrics;
use Visualsoft\Mantle\Contracts\ProvidesMantleEventDrivenUsageMetrics;

class WishlistItem extends Model implements ProvidesMantleEventDrivenUsageMetrics
{
    use MantleEventDrivenUsageMetrics;

    protected $mantleUsageMetricApiEventName = 'wishlist_item_metric';

    // Toggle which Eloquent events fire usage metrics
    protected static $mantleMetricsForCreated = true;
    protected static $mantleMetricsForUpdated = false;  // default
    protected static $mantleMetricsForDeleting = true;

    // Route to the owning user/shop
    public function getMantleMetricsUser()
    {
        return $this->wishlist->user;
    }

    // Customise the created payload
    public function getMantleUsageMetricPayloadCreated(): array
    {
        return array_merge(parent::getMantleUsageMetricPayloadCreated(), [
            'wishlist_id' => $this->wishlist_id,
            'product_id' => $this->product_id,
        ]);
    }
}
```

Payload methods available per action: `getMantleUsageMetricPayloadCreated()`, `getMantleUsageMetricPayloadUpdated()`, `getMantleUsageMetricPayloadDeleting()`.

Default `getMantleMetricsUser()` returns `$this->user` — override when the model reaches the user through a different relationship.

## Tracking feature usage

To increment local feature usage counters (separate from submitting usage events to Mantle):

```php
(new AdhocUsageMetricService)
    ->for($shop)
    ->logUsage(1, 'feature_key');
```

This updates the `mantle_feature_usage` table which `isFeatureLimitExceeded()` reads from.

## Configuration

### Required environment variables

| Variable | Purpose |
|---|---|
| `MANTLE_APP_ID` | App ID from Mantle API keys |
| `MANTLE_API_KEY` | API key from Mantle API keys |

### Common optional variables

| Variable | Default | Purpose |
|---|---|---|
| `MANTLE_USAGE_METRICS_QUEUE_NAME` | `mantle_usage_metrics` | Queue name for usage metric jobs |
| `MANTLE_USAGE_METRICS_APP_EVENT_NAME` | `vs_mantle_usage_metrics` | Fallback API event name |
| `MANTLE_CLIENT_TIMEOUT` | `5` | Guzzle request timeout (seconds) |
| `MANTLE_CLIENT_CONNECT_TIMEOUT` | `10` | Guzzle connection timeout (seconds) |
| `MANTLE_DEFAULT_PLAN_UUID` | `null` | UUID of plan to assign on app install |
| `MANTLE_SAVE_SHOP_METAFIELDS` | `false` | Also write plan metafields to the Shopify shop |
| `MANTLE_HELP_DESK_ENABLED` | `true` | Enable the Mantle Help Desk widget |

Point `vs-mantle.user_model` at your User class (no env var — set in config).

## Publishing assets

```bash
# Migrations (required)
php artisan vendor:publish --tag=visualsoft-assets-migrations
php artisan migrate

# Views (optional — to customise Blade templates)
php artisan vendor:publish --tag=visualsoft-assets
```

## Routes

The package auto-registers these routes:

| Route | Purpose |
|---|---|
| `GET app/mantle/plans` | Hosted billing / plans page |
| `GET app/mantle/subscription-postback` | Post-subscription redirect |
| `POST app/mantle/create-session` | Create hosted billing session |
| `POST mantle/webhook/{type?}` | Webhook ingestion from Mantle |
| `POST api/mantle/subscription-status` | Subscription status endpoint |
| `GET api/mantle/sync-status` | Mantle sync status |

Link to the plans page in your Shopify admin frontend:

```jsx
<Link to="/app/mantle/plans">Plans</Link>
```

`Plans.jsx` and `PlansInertia.jsx` embed billing via `HostedBillingContent` (nested iframe), preserving `ui-nav-menu` in embedded Shopify apps. Do not navigate the app iframe to the hosted session URL with `open(session.url, '_self')`.

Set the Mantle hosted billing return URL to `/app/mantle/subscription-postback`.

## Blade directives & components

- `@mantleCustomerBehaviourTracking` — renders the Mantle behaviour tracking script (requires `MANTLE_CUSTOMER_BEHAVIOUR_TRACKING_TOKEN`).
- `@mantleHelpDeskWidget` or `<x-mantle-help-desk-widget />` — renders the Help Desk support widget (requires `MANTLE_HELP_DESK_WIDGET_ID` and `MANTLE_JWT_SECRET`).

## Queue & scheduler requirements

- A queue worker must process the `mantle_usage_metrics` queue (or your configured name) for usage events to reach Mantle.
- Laravel's scheduler must be running. The package schedules: `model:prune` for log tables, `mantle:sync-customer-status`, `mantle:run-aggregations`, `mantle:check-expired-customers`. Cron expressions are configurable via `vs-mantle` config.

## Important constraints

- Plan and feature data is webhook-driven. Never insert or update rows in `mantle_plans`, `mantle_features`, or `mantle_plans_to_features` directly.
- The `mantles` record for a user is auto-created on first retrieval via the `MantleModel` trait's `retrieved` hook. Do not create it manually.
- The `users.active` column is managed by `ActivateStoreAction` / `DeactivateStoreAction` — do not set it directly.
