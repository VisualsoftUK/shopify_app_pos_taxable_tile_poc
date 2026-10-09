# Shop Context

## Canonical context paths

- Request/runtime context: `CurrentAppInstallation::user()`
- Explicit service/action context: pass `VsShopModel` directly as an argument
- Domain-based queue context: resolve via `ShopResolverInterface` / `BaseJob::getShopForDomain()`
- Legacy object helpers: `ShopifyObject::setUser(...)` for queued/static object flows

## Rules

- Prefer explicit `VsShopModel` arguments in services, actions, and jobs.
- Use request facade context only when request-bound behavior is intentional.
- In jobs, never assume `Auth::user()` exists — hydrate context from domain/model.

## Common failure modes

- Wrong shop used after asynchronous dispatch.
- Missing installation/shop IDs when static object helpers run in workers.
- Resolver returns a non-`VsShopModel` model instance.
