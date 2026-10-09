# App Review

## Overview

The app review feature determines whether to prompt the merchant to review the app. Built-in checks run automatically; consuming apps add custom checks via config.

## API routes

| Method | Route | Purpose |
|---|---|---|
| `GET` | `api/admin/app-review/should-show` | Returns `{ should_show: bool }` |
| `POST` | `api/admin/app-review` | Records the review result |

## `ReviewCheck` interface

```php
namespace Visualsoft\ShopifyAdmin\Contracts;

interface ReviewCheck
{
    public function passes(VsShopModel $shop): bool;
}
```

Every check (built-in or custom) must implement this. Return `true` if the check passes (i.e. it is OK to show the review prompt for this criterion).

## Built-in checks (always run)

`ShouldShowAppReview` always prepends `ReviewLogStatus`, then appends app-configured checks:

| Check | What it verifies |
|---|---|
| `ReviewLogStatus` | Previous review log entries (repeated dismissals, successful reviews — see class PHPDoc for rules) |
| `MinimumInstallDays` | Shop `installed_at` when present, else `created_at`, is older than `vs-admin-app-review.minimum_install_days`. Dates are parsed with Carbon, so an uncast datetime string still works. |
| `HasActivePlan` | Shop model implements `MantleableModel` and `hasActiveMantlePlan()` is true |

## Adding custom checks

Register implementing classes in the consuming app's config (or `config/vs-admin-app-review.php`):

```php
// config/vs-admin-app-review.php
return [
    'checks' => [
        \App\Domain\Checks\HasCompletedOnboarding::class,
        \App\Domain\Checks\HasMinimumOrders::class,
    ],
    'minimum_install_days' => env('APP_REVIEW_MINIMUM_INSTALL_DAYS', 7),
];
```

Each class must implement `Visualsoft\ShopifyAdmin\Contracts\ReviewCheck`. The package throws `InvalidReviewCheckException` if a configured class does not implement the interface.

## React integration

```jsx
import RequestAppReview from '@admin/Components/AppReview/RequestAppReview';
```

Use `Services/AppReview/RequestReview.js` (accessible via `@admin`) to call the API routes from the frontend.

## Shop model: `installed_at`

The package publishes a migration that adds `users.installed_at` and backfills it from `created_at`. Consuming apps should still wire the column on the shop User model:

```php
protected $fillable = [
    // ...
    'installed_at',
];

protected $casts = [
    // ...
    'installed_at' => 'datetime',
];
```

`installed_at` must be fillable so `SetInstalledAt` can persist install/reinstall time via `update()`. The datetime cast is recommended so the attribute is a Carbon instance throughout the app. `MinimumInstallDays` also parses uncast datetime strings, so a missing cast does not break the should-show endpoint.

## Notes

- `AppReviewLog` model (table `app_review_log`) is managed by the package — do not write to it directly; use the `POST api/admin/app-review` endpoint.
- `HasActivePlan` requires the shop model to use the `vs-mantle` `MantleableModel` contract. Activate `vs-mantle` skill if needed.
