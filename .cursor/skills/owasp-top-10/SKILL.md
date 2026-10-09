---
name: owasp-top-10
description: >-
  Enforce OWASP Top 10 (2025) secure coding practices when generating, reviewing, or refactoring application code.
  Covers broken access control, security misconfiguration, supply chain, cryptographic failures, injection, insecure
  design, authentication failures, data integrity failures, logging/alerting failures, and exception handling.
  Use when writing security-sensitive code, reviewing code for vulnerabilities, generating authentication or
  authorization logic, handling user input, managing secrets, processing external data, or when the user mentions
  OWASP, security review, or secure coding.
---

# OWASP Top 10 (2025) Secure Coding

Apply these rules when generating, reviewing, or refactoring application code to prevent the OWASP Top 10 vulnerability categories. Each rule file is a self-contained directive — read only the ones relevant to the current task.

## Quick Reference

| # | OWASP ID | Category | Rule file |
|---|----------|----------|-----------|
| 1 | A01:2025 | Broken Access Control | `rules/broken-access-control.md` |
| 2 | A02:2025 | Security Misconfiguration | `rules/security-misconfiguration.md` |
| 3 | A03:2025 | Software Supply Chain Failures | `rules/software-supply-chain-failures.md` |
| 4 | A04:2025 | Cryptographic Failures | `rules/cryptographic-failures.md` |
| 5 | A05:2025 | Injection | `rules/injection.md` |
| 6 | A06:2025 | Insecure Design | `rules/insecure-design.md` |
| 7 | A07:2025 | Authentication Failures | `rules/authentication-failures.md` |
| 8 | A08:2025 | Software / Data Integrity Failures | `rules/software-data-integrity-failures.md` |
| 9 | A09:2025 | Security Logging & Alerting Failures | `rules/security-logging-alerting-failures.md` |
| 10 | A10:2025 | Mishandling Exceptional Conditions | `rules/mishandling-exceptional-conditions.md` |

## How to Apply

1. Identify which vulnerability categories are relevant to the current task using the table above.
2. Read the corresponding rule file(s) — each contains mandatory directives and a code-generation checklist.
3. Apply the directives and run the checklist before outputting code.

### Task-to-rule mapping

| Task involves… | Read rule(s) |
|----------------|-------------|
| Authorization, access control, policies, IDOR | A01 Broken Access Control |
| Config, headers, CORS, cookies, debug mode, XXE | A02 Security Misconfiguration |
| Adding dependencies, CDN assets, third-party APIs | A03 Supply Chain |
| Encryption, hashing, passwords, keys, tokens | A04 Cryptographic Failures |
| Database queries, OS commands, user input, eval | A05 Injection |
| Business logic, state machines, rate limiting, recovery flows | A06 Insecure Design |
| Login, session management, MFA, JWTs, brute-force | A07 Authentication Failures |
| Deserialization, webhooks, signed payloads, plugins | A08 Data Integrity |
| Logging, audit trails, error messages, anomaly detection | A09 Logging & Alerting |
| Error handling, transactions, resource cleanup, fail-closed | A10 Exceptional Conditions |

### Scope note

All rules are framework-agnostic and apply to any language or stack.
