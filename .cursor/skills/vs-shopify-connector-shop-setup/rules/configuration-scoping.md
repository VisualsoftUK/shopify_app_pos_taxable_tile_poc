# Configuration and Shop Scoping

## Package config surfaces

The service provider merges these config keys — start from package defaults and override in app config/env only where needed:

- `vs-shopify-connector`
- `vs-shopify-connector-plan`
- `vs-shopify-graphql-defaults`
- `vs-shop-scoping`
- `vs-brackets`
- `shopify-flow`
- `vs-shopify`

## Shop scoping flow

- Middleware: `Http\Middleware\ApplyShopScoping`
- Contract: `Contracts\ShopScoped`
- Model concern: `Concerns\HasShopScope`
- Config list: `config('vs-shop-scoping.models')`

Rules:
- Only include models in `vs-shop-scoping.models` that implement `ShopScoped`.
- Use `HasShopScope` defaults unless your model needs custom scope logic.
- Use `withoutShopScope()` deliberately and sparingly.

## Common mistakes to avoid

- Mixing package scoping concerns with unrelated app domain filtering.
- Registering non-Eloquent/non-`ShopScoped` classes in scoping config.
- Treating `vs-shopify-connector-plan.php` as app billing architecture source.
