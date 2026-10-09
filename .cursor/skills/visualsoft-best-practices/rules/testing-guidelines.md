# Testing Guidelines

Visualsoft-specific testing conventions. These extend `pest-testing` — when there is a conflict, these take precedence.

## Factories

- If `UserFactory` has a `withMantlePlan` state, apply it by default when calling `User::factory()` unless the test is specifically asserting behaviour for a user *without* a Mantle plan.

## Inertia Deferred Props

Deferred/optional Inertia props require two separate tests — one asserting the prop is absent on initial load, and one using `reloadOnly` to assert the deferred response:

```php
test('deferred prop is absent on initial page load', function () {
    $this->asShop()
        ->getJson(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Dashboard')
            ->missing('stats')
        );
});

test('deferred prop returns correct data on reload', function () {
    $this->asShop()
        ->get($this->routeWithShopifyToken('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Dashboard')
            ->missing('stats')
            ->reloadOnly('stats', fn (AssertableInertia $reload) => $reload
                ->has('stats', 3)
                ->etc()
            )
        );
});
```

- Always include `->etc()` inside `reloadOnly` assertions to avoid false failures from additional props.
