# Eat-in cart transform

Rust Shopify Function (`cart.transform.run`, API 2026-07, `shopify_function` 2.2) that adds a per-unit
"Eat-in charge" to dynamic-VAT products when the cart is Eat In.

## Input (`src/cart_transform_run.graphql`)

- Cart attribute `_order_type` (`eat_in` | `takeaway`). Missing or unrecognised: falls back to `defaultOrderType`.
- Per line: id, unit price, variant id, product title and product metafields `vat.behaviour`
  (`standard` | `zero` | `dynamic`) and `vat.eat_in_uplift_percent` (optional `number_decimal`).
- The `$app:eat-in` / `config` JSON metafield on the CartTransform object:

```json
{"eatInChargeVariantId":"gid://shopify/ProductVariant/123","defaultUpliftPercent":20,"defaultOrderType":"takeaway"}
```

`eatInChargeVariantId` is required. `defaultUpliftPercent` defaults to 20. `defaultOrderType` defaults to `takeaway`.

## Output

- Takeaway carts, or a missing config or charge variant id: no operations.
- Eat In carts: one `lineExpand` per `dynamic` line, titled `Eat In: {product title}`, with two expanded items per unit:
  1. the original variant at its unit price (`fixedPricePerUnit`);
  2. the eat-in charge variant at `round_half_up(unit_price * uplift% / 100)` to 2dp, with attributes
     `_eat_in_for` (original variant gid) and `_eat_in_uplift_percent` (for example `20`).
- The uplift comes from the product's `vat.eat_in_uplift_percent` when it is above 0. Otherwise it comes from `defaultUpliftPercent`.
- These lines are skipped: the eat-in charge variant itself, non-variant merchandise, zero-priced lines, and lines whose surcharge rounds to 0.00.
- The surcharge is calculated with exact integer decimal arithmetic. The input amount is read as a string, not an `f64`.

## Development

```sh
cargo test                                               # unit tests
cargo build --target=wasm32-unknown-unknown --release    # same as the [extensions.build] command
```

`schema.graphql` comes from Shopify's `extensions-templates` repo (`functions-cart-transform-rs`, api_version 2026-07).
To refresh it, run `shopify app function schema`.
