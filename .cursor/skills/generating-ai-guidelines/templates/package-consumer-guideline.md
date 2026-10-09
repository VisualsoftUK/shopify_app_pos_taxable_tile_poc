# Template: Package Consumer Guideline

File: `resources/boost/guidelines/{area}.blade.php`

One file per topic. Boost compiles every `.blade.php` in `resources/boost/guidelines/`. Do not append long sections to `core.blade.php` — that file is for short global pointers only.

If agents should activate a skill whenever they work in this area, add a one-line pointer in `core.blade.php` (Skills section). Do not copy the topic rules there.

```blade
## Checkout Flow

- Orders move through states: `pending → processing → complete | failed`.
- Always use `OrderStateMachine::transition($order, $newState)` — never update `status` directly.
- Refunds must go through `RefundService`; partial refunds require a `RefundData` object.

### Skills
- Activate `checkout-flow` when working with orders, payments, or refunds.
```
