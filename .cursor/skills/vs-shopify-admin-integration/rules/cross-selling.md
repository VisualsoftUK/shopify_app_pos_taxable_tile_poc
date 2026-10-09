# Cross-Selling

## Overview

Cross-selling displays related Shopify apps fetched from the VS API. Click events are tracked via a beacon → Redis → scheduled command pipeline. This feature does **not** use queued jobs; it uses a scheduled Artisan command.

## PHP — displaying apps

The `GET api/admin/cross-selling` route is handled by `CrossSellingController@index`, which calls:

```php
CrossSellingService::getApps(string $locale): array
```

`CrossSellingService` calls `VsApiClient::getApps($locale)`. Results are cached per `vs-admin-cross-selling.cache_expiry_minutes`.

The number of apps shown is controlled by `vs-admin-cross-selling.number_of_apps_to_show`.

## Redis click tracking pipeline

1. **Beacon** — `CrossSellingClickTracking` handler fires on the `cross-selling-click` beacon type. It calls `Redis::rpush('cross_selling_clicks', json_encode($data))`.
2. **Data shape** — `CrossSellAppClickData` (Spatie Data): `from_app_id`, `to_app_id`, `shop`, `environment`.
3. **Scheduler** — `ProcessClicks` (`vs-admin:process-cross-sell-clicks`) runs on the cron from `vs-admin-cross-selling.process_cross_sell_clicks_cron_expression`, with `withoutOverlapping(vs-admin-cross-selling.without_overlapping_minutes)`. It `lpop`s up to `--limit` (default 1000) entries and calls `CrossSellingClickTrackingService::trackClick`.
4. **`BEACONS_ENABLED=true`** is required in `.env` for the beacon handler to be active.

## Config keys

```php
// config/vs-admin-cross-selling.php (in consuming app — override defaults)
'number_of_apps_to_show' => env('CROSS_SELLING_NUMBER_OF_APPS_TO_SHOW', 3),
'cache_expiry_minutes'   => env('CROSS_SELLING_CACHE_EXPIRY_MINUTES', 60),
'vsapi_app_id'           => env('CROSS_SELLING_VSAPI_APP_ID'),
'process_cross_sell_clicks_cron_expression' => env('CROSS_SELLING_PROCESS_CROSS_SELL_CLICKS_CRON_EXPRESSION', '* * * * *'),
'without_overlapping_minutes' => env('CROSS_SELLING_WITHOUT_OVERLAPPING_MINUTES', 10),
```

`VS_API_SECRET` must also be set (under `vs-admin-services.vsapi.secret`).

## React components

Import from the package's `@admin` alias:

```jsx
import CrossSellingApp from '@admin/Components/CrossSelling/CrossSellingApp';
import CrossSellingCard from '@admin/Components/CrossSelling/CrossSellingCard';
```

`CrossSellingApp` renders the list of `CrossSellingCard` components. Pass the `apps` array returned by the `cross-selling` API route.

Include the cross-selling CSS in your Vite entry or layout:

```js
import '@admin-css/cross-selling/main.css';
// or via package.json export: 'visualsoft/vs-shopify-admin/css/cross-selling/main.css'
```

## Beacon registration

The `CrossSellingClickTracking` handler is merged automatically into `config('beacons.handlers')` by `AdminServiceProvider`. No manual registration is required. Ensure `vs-shopify-beacons-integration` is installed and configured.
