# Template: Package Consumer Skill

File: `resources/boost/skills/{slug}-{area}/SKILL.md`

Follows the same structure as an app skill. Use a `rules/` subdirectory when the domain has multiple distinct sub-topics — the agent reads only the rule file relevant to the current task.

```markdown
---
name: vs-example-checkout-flow
description: Covers order state transitions, payment processing, and refunds. Use when working with orders, OrderStateMachine, payments, or RefundService.
---

# Checkout Flow

## Quick reference

| Concept | Class |
|---|---|
| State transitions | `OrderStateMachine` |
| Payment capture | `CapturePaymentAction` |
| Refunds | `RefundService` |

## Sub-topics

- For order state rules, see [rules/order-states.md](rules/order-states.md)
- For payment gateway integration, see [rules/payments.md](rules/payments.md)
```
