# Template: Contributor Skill (Multiple Domains)

Use this pattern when the skill covers more than one distinct domain. The `SKILL.md` becomes a lightweight index; full detail lives in `rules/` files loaded only when relevant.

## Directory structure

```
.ai/skills/{slug}-{area}/
├── SKILL.md          ← index only; quick-reference + list of rule files
└── rules/
    ├── {domain-a}.md
    ├── {domain-b}.md
    └── {domain-c}.md
```

The directory name and the `name` field in frontmatter both use `{slug}-{area}` to prevent conflicts when multiple packages install skills into the same `.cursor/skills/` directory.

## SKILL.md (index)

Keep this under 50 lines. Its only job is orientation and routing.

```markdown
---
name: vs-example-webhooks
description: Covers vs-example package internals — webhook events, API clients, and usage metrics. Use when adding or modifying events, listeners, clients, or metrics in this package.
---

# VS-Example (Contributor)

## Quick reference

| Concept | Key class(es) |
|---|---|
| Service provider | `ExampleServiceProvider` |
| Webhook ingestion | `WebhookController` → `$topicToEventMap` |
| App-scoped API | `ExampleAppClient` |
| Usage metrics | `AdhocUsageMetricService`, `BaseAggregation` |

## Rule files

Always use a sub-agent to read the relevant rule file before working in that domain.

### 1. Webhook events → `rules/webhook-events.md`

- Adding new webhook event/listener pairs
- Webhook auth middleware and test pattern

### 2. API clients → `rules/api-clients.md`

- Dual-client pattern (`AppClient` vs `CustomerClient`)
- Container bindings and authentication

### 3. Usage metrics → `rules/usage-metrics.md`

- Adhoc, aggregated, and event-driven pipelines
- Event submission chain
```

## rules/{domain}.md

Each rule file covers one domain in full detail. Same quality bar as a single-domain skill.

```markdown
# Webhook Events

## Adding a new webhook event

1. Create `src/Events/YourNewEvent.php` extending `AbstractExampleEvent`.
2. Create `src/Listeners/YourNewListener.php`.
3. Add the topic → event mapping in `WebhookController::$topicToEventMap`.
4. Register in `ExampleServiceProvider::registerWebhookHandlingEvents()`.
5. Add a Feature test covering webhook delivery and a Unit test for listener logic.

## Webhook flow

1. POST arrives at `/example/webhook/{type?}` with HMAC-signed payload.
2. `ExampleAuthWebhook` middleware validates the signature.
3. `WebhookController` maps `topic` → event class and dispatches it. Unknown topics fire `UnhandledTopic`.

## Test pattern

```php
it('handles your new webhook', function () {
    Event::fake();

    $payload = ['topic' => 'your/topic', 'data' => [...]];
    $timestamp = now()->timestamp;
    $hmac = createValidHmacSignature($payload, $timestamp);

    $this->postJson('/example/webhook/your-topic', $payload, [
        'x-example-hmac-sha256' => $hmac,
        'x-timestamp' => $timestamp,
    ])->assertOk();

    Event::assertDispatched(YourNewEvent::class);
});
```
```
