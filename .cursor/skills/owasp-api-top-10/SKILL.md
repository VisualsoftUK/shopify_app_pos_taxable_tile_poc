---
name: owasp-api-top-10
description: >-
  Enforce OWASP API Security Top 10 (2023) secure coding practices when generating, reviewing, or refactoring API
  code. Covers object-level authorization, authentication, property-level authorization, resource consumption,
  function-level authorization, business flow abuse, SSRF, security misconfiguration, inventory management, and
  unsafe API consumption. Use when building or reviewing REST/GraphQL APIs, designing API authorization or
  authentication, handling API payloads, integrating third-party APIs, versioning endpoints, or when the user
  mentions OWASP API, API security, or secure API design.
---

# OWASP API Security Top 10 (2023) Secure Coding

Apply these rules when generating, reviewing, or refactoring API code to prevent the OWASP API Security Top 10 vulnerability categories. Each rule file is a self-contained directive — read only the ones relevant to the current task.

## Quick Reference

| # | OWASP ID | Category | Rule file |
|---|----------|----------|-----------|
| 1 | API1:2023 | Broken Object Level Authorization | `rules/broken-object-level-authorization.md` |
| 2 | API2:2023 | Broken Authentication | `rules/broken-authorization.md` |
| 3 | API3:2023 | Broken Object Property Level Authorization | `rules/broken-object-property-level-authorization.md` |
| 4 | API4:2023 | Unrestricted Resource Consumption | `rules/unrestricted-resource-consumption.md` |
| 5 | API5:2023 | Broken Function Level Authorization | `rules/broken-function-level-authorization.md` |
| 6 | API6:2023 | Unrestricted Access to Sensitive Business Flows | `rules/unrestricted-access-sensitive-business-flows.md` |
| 7 | API7:2023 | Server-Side Request Forgery (SSRF) | `rules/server-side-request-forgery.md` |
| 8 | API8:2023 | Security Misconfiguration | `rules/security-misconfiguration.md` |
| 9 | API9:2023 | Improper Inventory Management | `rules/improper-inventory-management.md` |
| 10 | API10:2023 | Unsafe Consumption of APIs | `rules/unsafe-consumption-apis.md` |

## How to Apply

1. Identify which vulnerability categories are relevant to the current task using the table above.
2. Read the corresponding rule file(s) — each contains mandatory directives and a code-generation checklist.
3. Apply the directives and run the checklist before outputting code.

### Task-to-rule mapping

| Task involves… | Read rule(s) |
|----------------|-------------|
| Object lookups by ID, IDOR, tenant scoping, ownership checks | API1 Broken Object Level Authorization |
| Login, tokens, JWTs, session management, brute-force, MFA | API2 Broken Authentication |
| Mass assignment, DTOs, serialization, property filtering | API3 Broken Object Property Level Authorization |
| Pagination limits, payload sizes, rate limiting, timeouts, GraphQL depth | API4 Unrestricted Resource Consumption |
| Role-based access, admin endpoints, HTTP method restrictions, RBAC | API5 Broken Function Level Authorization |
| Checkout flows, anti-automation, CAPTCHA, state machines, business caps | API6 Unrestricted Access to Sensitive Business Flows |
| Outbound requests, URL validation, redirect following, internal network access | API7 Server-Side Request Forgery |
| CORS, debug mode, error responses, security headers, cookie flags | API8 Security Misconfiguration |
| API versioning, deprecated endpoints, environment isolation, shadow parameters | API9 Improper Inventory Management |
| Third-party API ingestion, upstream validation, TLS verification, output encoding | API10 Unsafe Consumption of APIs |

### Scope note

All rules are framework-agnostic and apply to any language or stack.
