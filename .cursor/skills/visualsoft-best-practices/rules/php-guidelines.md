# PHP Guidelines

Visualsoft-specific PHP conventions. These extend `laravel-best-practices` — when there is a conflict, these take precedence.

## Code Style

- Avoid nested ternaries — use early returns or named variables instead.
- **Early returns over nested if/else**: the final statement of a method should be the expected happy-path result; return early for guard clauses.

## Controllers

- Only the standard resource methods are permitted: `index`, `create`, `store`, `edit`, `update`, `destroy`.
- A `restore` method is also allowed for soft-delete/archive flows.
- Routes that don't map to a resource method → single-action invokable controller.
- Method parameter order:
  1. Form Request (validation failure short-circuits before anything else loads)
  2. Other injected classes, alphabetically
  3. Route parameters, in route definition order

```php
public function update(UpdateMediaRequest $request, MediaService $service, Media $media): RedirectResponse
```

## Action Classes

- Static constructor + one trigger method only (e.g. `get`, `create`, `update`).
- Call as: `GetExampleListAction::for($params)->get()`
- Actions may call other actions; keep each action single-purpose.

```php
class CreateMediaAction
{
    public static function for(Request $request): static { ... }
    public function create(): Media { ... }
}
```

## File & Class Suffixes

Follow the pattern for easy discovery — especially when multiple types relate to the same domain concept:

| Type | Suffix |
|---|---|
| Controller | `Controller` |
| Form Request | `Request` |
| Job | `Job` |
| Action | `Action` |
| Service | `Service` |
| Data Object | `Data` |
| Interface | `Interface` (or descriptive noun) |
| Abstract class | `Abstract` prefix |

Models are the exception — use the singular table name with no suffix (e.g. `User`, `Order`).

## Data Objects

- Extend `Spatie\LaravelData\Data` from `spatie/laravel-data`.
- Return Data objects instead of plain associative arrays.

## Laravel Preferences

- Use the modern Attribute syntax on models (not `getCustomFieldAttribute` style).
- Prefer collections over arrays.
