---
name: vs-shopify-beacons-integration
description: Consumer knowledge for integrating vs-shopify-beacons into a Shopify app. Use when installing the package, implementing beacon handlers, configuring scheduling, or testing beacon flows.
---

# vs-shopify-beacons Integration

## Quick reference

| Concept | Class / Key |
|---|---|
| Handler contract | `Visualsoft\ShopifyBeacons\Contracts\BeaconHandlerInterface` |
| Beacon DTO | `Visualsoft\ShopifyBeacons\DataObjects\BeaconData` |
| Handler result | `Visualsoft\ShopifyBeacons\DataObjects\BeaconProcessResult` |
| Error model | `Visualsoft\ShopifyBeacons\Models\BeaconErrors` |
| Handler config key | `config('beacons.handlers')` |
| Process command | `php artisan beacons:process` |

## Install checklist

1. Require the package: `composer require visualsoft/vs-shopify-beacons`.
2. Publish the migration:
   ```bash
   php artisan vendor:publish --tag=shopify-beacons-assets-migrations
   php artisan migrate
   ```
3. Create `config/beacons.php` in the app and register handlers (see below).
4. Set environment variables.
5. Ensure the required middleware names exist on the router (see Routes section).

## Config (`config/beacons.php`)

```php
return [
    'enabled' => env('BEACONS_ENABLED', true),
    'process_cron_expression' => env('BEACONS_PROCESS_CRON_EXPRESSION', '*/1 * * * *'),
    'process_without_overlapping_minutes' => env('BEACONS_PROCESS_WITHOUT_OVERLAPPING_MINUTES', 10),
    'process_limit' => env('BEACONS_PROCESS_LIMIT', 1000),
    'handlers' => [
        'click' => \App\Beacons\Handlers\ClickHandler::class,
        'serve' => \App\Beacons\Handlers\ServeHandler::class,
    ],
];
```

The `handlers` array **must** be provided by the consuming app. Keys are the `beaconType` strings sent by the client; values are handler class names.

## Environment variables

| Variable | Default | Purpose |
|---|---|---|
| `BEACONS_ENABLED` | `true` | Enables/disables scheduled processing |
| `BEACONS_PROCESS_CRON_EXPRESSION` | `*/1 * * * *` | How often `beacons:process` runs |
| `BEACONS_PROCESS_WITHOUT_OVERLAPPING_MINUTES` | `10` | Overlap prevention window |
| `BEACONS_PROCESS_LIMIT` | `1000` | Max beacons processed per run |

## Implementing a handler

```php
namespace App\Beacons\Handlers;

use Visualsoft\ShopifyBeacons\Contracts\BeaconHandlerInterface;
use Visualsoft\ShopifyBeacons\DataObjects\BeaconData;
use Visualsoft\ShopifyBeacons\DataObjects\BeaconProcessResult;

class ClickHandler implements BeaconHandlerInterface
{
    public function handle(BeaconData $beaconData): BeaconProcessResult
    {
        // $beaconData->userId      — authenticated user id
        // $beaconData->beaconType  — e.g. 'click'
        // $beaconData->payload     — array from client
        // $beaconData->timestamp   — ISO 8601 string (set at ingest time)

        // Return success
        return new BeaconProcessResult(success: true, errors: [], logError: false);

        // Return failure — logs to beacon_errors
        // return new BeaconProcessResult(success: false, errors: ['message'], logError: true);
    }
}
```

- `logError: true` + `success: false` + non-empty `errors` → row written to `beacon_errors`.
- `logError: false` → failure is silent (no DB row).

## Routes

Three endpoints are registered automatically:

| Route | Middleware | Name |
|---|---|---|
| `POST /proxy/beacon` | `auth.proxy` | `proxy.beacon` |
| `POST /checkout-pixel/beacon` | `auth.checkout_pixel` | `checkout-pixel.beacon` |
| `POST /api/admin/beacon` | `admin` (falls back to `verify.shopify`) | `vs-shopify-admin.beacon` |

The middleware aliases `auth.proxy`, `auth.checkout_pixel`, and `verify.shopify` must be registered on the router by the host app (typically via the VS Shopify Connector).

## Frontend helpers

### App-level JS (Vite)

Add a `@beacons` alias to `vite.config.js` if it isn't already present:

```js
resolve: {
    alias: {
        '@beacons': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-beacons/resources'),
    },
},
```

Then import the helpers directly from the package:

```js
import { triggerBeacon, triggerFetchBeacon } from '@beacons/beacon/index.js';

// Fire-and-forget via navigator.sendBeacon (no auth header)
triggerBeacon('click', { productId: 123 }, '/proxy/beacon');

// Fetch with optional Bearer token (keepalive: true)
triggerFetchBeacon('serve', { productId: 123 }, '/api/admin/beacon', sessionToken);
```

`triggerBeacon` uses `navigator.sendBeacon` — suitable for proxy/pixel endpoints where no auth header is needed.
`triggerFetchBeacon` sends a `fetch` with `keepalive: true` and an optional `Authorization: Bearer` header.

### Shopify extensions

To use the beacon helpers inside a Shopify extension, the package must be exposed as a workspace dependency.

**1. Root `package.json`** — add the package to `workspaces`:

```json
{
    "workspaces": [
        "vendor/visualsoft/vs-shopify-beacons/resources/*"
    ]
}
```

**2. Extension `package.json`** (e.g. `extensions/main/package.json`) — add as a dependency:

```json
{
    "dependencies": {
        "@shopify-app/beacon": "*"
    }
}
```

Then run `npm install` (or `yarn`) from the root to link the workspace. Import in the extension the same way:

```js
import { triggerBeacon, triggerFetchBeacon } from '@shopify-app/beacon';
```

## Testing beacon flows

```php
use Illuminate\Support\Facades\Redis;
use Visualsoft\ShopifyBeacons\Models\BeaconErrors;

// Assert a beacon was pushed to Redis
Redis::shouldReceive('rpush')
    ->once()
    ->with('beacons', \Mockery::on(fn ($json) => str_contains($json, '"beaconType":"click"')));

// Assert a failure was persisted
$this->assertDatabaseHas('beacon_errors', [
    'beacon_type' => 'click',
    'user_id' => $user->id,
]);

// Assert no failures
$this->assertDatabaseCount('beacon_errors', 0);
```

For command tests, mock Redis and assert the return code:

```php
Redis::shouldReceive('llen')->once()->with('beacons')->andReturn(0);

$result = $this->artisan('beacons:process');
$result->assertSuccessful();
```
