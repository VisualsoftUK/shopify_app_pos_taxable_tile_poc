# Setup Guide

## Overview

The setup guide tracks onboarding steps per shop. Steps are stored in `set_up_steps` (published migration). The consuming app defines what steps exist; the package persists completion state.

## Migration

Published via `php artisan vendor:publish --tag=visualsoft-assets-migrations`. Creates table `set_up_steps` with columns: `user_id`, `step_key` (string), `completed` (boolean), `meta` (nullable JSON).

## PHP — actions

### `GetSetUpSteps`

```php
GetSetUpSteps::for(ShopModel $shop)->get(): array
```

Returns the shop's `set_up_steps` rows as an array. If no rows exist yet, returns an empty array.

### `SaveSetUpStep`

```php
SaveSetUpStep::for(
    shop: ShopModel $shop,
    stepKey: string $stepKey,
    completed: bool $completed = false,
    meta: ?array $meta = null
)->save(): mixed
```

Calls `$shop->setUpSteps()->updateOrCreate(['step_key' => $stepKey], [...])`. After saving, `SetUpController` redirects to the named route `home` (303) — **the consuming app must define a `home` named route**.

## API route

`POST api/admin/setup-steps` — handled by `SetUpController`. Requires `SaveSetUpStepRequest` (validates `step_key`, `completed`).

## Model

`Visualsoft\ShopifyAdmin\Models\Common\SetUpStep` uses `HasShopScope` and belongs to the shop via `user_id`. Use `$shop->setUpSteps` to access the relationship (defined on the shop model via the package's concern).

## React component

```jsx
import SetupGuide from '@admin/Components/Common/SetupGuide';
import SetupGuideSkeleton from '@admin/Components/Common/SetupGuideSkeleton';
```

- `SetupGuide` — renders the list of setup steps. Expects a `steps` prop (array of step objects with `step_key`, `completed`, `meta`).
- `SetupGuideSkeleton` — loading state; render while the steps are being fetched.
- `SetupItem` — individual step row; used internally by `SetupGuide`.

Frontend API calls use `Services/Common/SetupGuide.js` (accessible via `@admin`).

## Notes

- Step keys are arbitrary strings; the consuming app controls what keys exist and what they mean.
- The `meta` field is a free-form JSON object — use it for step-specific state (e.g. selected values, timestamps).
