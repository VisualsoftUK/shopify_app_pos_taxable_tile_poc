# GraphQL Service

## Default GraphQL path

```php
ShopifyGraphQlService::for($shop)
    ->withQuery($query)
    ->withParams($params)
    ->maxRetries(3)
    ->retryDelay(1)
    ->get();
```

Defaults come from `config('vs-shopify-graphql-defaults.*')`.

## Mandatory convention

- Prefer this service path over direct `api()->graph()` for all new consuming-app workflows.
- Introduce or extend service classes rather than embedding raw GraphQL in controllers, components, or jobs.

## Error and retry expectations

- Throttle/rate-limit responses retry with configured delay automatically.
- Non-throttle failures surface as package exceptions (`ApiException` or wrapped domain exceptions).
- If the shop's `invalid_token` matches `password` (plaintext access token, compared with `===`), skip GraphQL entirely: do not call Shopify and do not retry. Return a 401 payload with `errors` set (`An invalid token was used.`) so callers can use `empty($response['errors'])` the same way as a failed GraphQL call.
- HTTP 401 responses persist `invalid_token` from the shop `password` and set `token_invalidated_at` to now, then throw `ApiAuthException`.
- A successful GraphQL call (token works) clears `invalid_token` and `token_invalidated_at` when those fields are set. The same clear runs on `AppInstalledEvent` (install / re-auth) and `AppUninstalledEvent`.
- When selecting shops to call GraphQL (scheduled sweeps, Artisan commands), exclude those shops with `User::query()->withoutKnownInvalidAccessToken()` (`HasShopifyIdentifiers` / `WithoutKnownInvalidAccessToken`).
- Include shop + query/mutation context in logs.

## Existing service options — check before creating new code

- `ShopifyGraphQlService` — fluent GraphQL calls, retry, cost/throttle logging.
- `ShopifyFileService` — file create + readiness polling flow.
- `UserSettingService` — user setting retrieval and metafield-backed behavior.

## Related rule files

- `rules/service-classes.md` — when to create a new service vs reuse an existing one
