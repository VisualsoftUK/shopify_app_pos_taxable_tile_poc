# Models and Relationships

## Package contracts to respect

- `Contracts\VsShopModel` is the key shop contract.
- Shop resolution relies on your auth provider user model (`auth.providers.users.model`) and the `name` column for domain lookup.
- Bracket and identifier behaviors should align with package concerns (`HasBracket`, `HasShopifyIdentifiers`).

## App model guidance

- Extend/implement connector-required behavior on your shop user model.
- Keep app-specific relationships on app models; do not fork package internals.
- If you introduce shop-scoped app models, pair `ShopScoped` + `HasShopScope` and register the model in `vs-shop-scoping.models`.

## Avoid

- Rewriting package assumptions for shop identity columns without updating resolver logic.
- Using this as a generic Eloquent relationships guide — focus on connector-specific contracts only.
