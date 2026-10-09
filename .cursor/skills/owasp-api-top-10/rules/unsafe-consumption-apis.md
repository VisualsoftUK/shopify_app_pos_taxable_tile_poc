# Secure API Coding: Unsafe Consumption of APIs (OWASP API10:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent the Unsafe Consumption of APIs vulnerability. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components that act as a client to ingest data from upstream APIs, third-party microservices, or external SaaS providers, you must actively prevent **Unsafe Consumption of APIs**. You have no access to network boundaries, forward proxies, or transport layer firewalls; all mitigations must be implemented directly within the application's ingestion code, validation structures, and data handling logic.

## 2. Vulnerability Context at the Application Level

This vulnerability occurs when an API trusts data received from external, third-party APIs without verifying it with the same rigor applied to raw user input. Developers often assume that because a connection is established with a reputable upstream vendor, the data received is inherently safe, structured correctly, and free from malicious payloads. At the code level, this failure typically happens when developers:
* Pass data retrieved from a third-party API directly to local database queries or raw SQL methods without parameterization (leading to secondary SQL injection).
* Pass data from external services straight to a frontend response model or HTML template rendering pipeline without context-aware output encoding (leading to stored Cross-Site Scripting / XSS).
* Blindly execute system shell commands or format system arguments using strings fetched from external integrations.
* Fail to implement local validation schemas for data fields returned by external webhooks or APIs, assuming the payload structural schema will never shift or include unexpected parameters.
* Fail to enforce execution and network response timeouts when interacting with integrated APIs, allowing slow-loris types of resource exhaustion to lock application worker threads.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, treating all upstream API connections as untrusted, external input boundaries:

### A. Zero-Trust Ingestion Schema Validation

* **Rule:** Upstream payloads must be structurally validated before any application action is taken on them.
* **Action:** Treat data returned from an external API exactly like raw user input from a public web form. Run the received JSON or XML through a strict, positive allowlist schema validator (e.g., JSON Schema, Zod, or native type-casting mappers) at the boundary. Reject or immediately drop fields that do not match the expected structural primitives.

### B. Parametric Data Handling (No Downstream Injection)

* **Rule:** Imported data fields must never interact dynamically with internal interpreters or data drivers.
* **Action:** When persisting or mapping data from an external API into a local database system, you must strictly utilize parameterized queries, prepared statements, or type-safe ORM abstraction tools. Never concatenate strings fetched from an external source into local executable logic or query parameters.

### C. Context-Aware Output Sanitization

* **Rule:** If external data is bound to public output models, it must be completely sanitized.
* **Action:** Before transferring data elements harvested from an integration to views, template contexts, or public API structures, apply context-aware output encoding. For example, if a third-party profile name field contains malicious script tags, ensure the data is HTML-escaped by the presentation layer to neutralize stored scripting threats.

### D. Enforce Strict Outbound Connection Transport & Timeouts

* **Rule:** Do not allow third-party latency to exhaust internal runtime resources.
* **Action:** Configure the HTTP execution client to pass explicit, short-duration request and stream timeouts (e.g., maximum 3000ms–5000ms) on all outward integration operations. Safely wrap these requests in `try-catch` exception logic to gracefully handle dead, slow, or unyielding external responses without leaking system threads.

### E. Explicit Transport Security Overrides

* **Rule:** TLS verification protocols must never be deactivated in code to force connectivity.
* **Action:** Forbid any configurations that disable SSL/TLS certificate verification (e.g., do not write code that sets `rejectUnauthorized: false` in Node.js or `verify=False` in Python). The application layer must strictly validate the integrity of the encrypted transport path to prevent man-in-the-middle data alterations.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any integration or external ingestion logic, run this internal validation across all layers:
- [ ] **Ingress Checking:** Is the incoming third-party payload explicitly validated against a precise validation schema at the entry boundary?
- [ ] **SQL/ORM Safety:** Are values extracted from third-party payloads completely parameterized in database operations rather than concatenated?
- [ ] **Output Isolation:** Is the data encoded or sanitized before being echoed to other end-users to prevent stored XSS or data pollution?
- [ ] **Explicit Timeouts:** Do the HTTP operations have short execution timeouts configured to prevent application hang-ups?
- [ ] **Cert Verification:** Is TLS certificate checking completely active, with no legacy bypassing blocks present in the transport code?

**Prioritize security over brevity. All generated API code interacting with external service integrations must demonstrate strict, untrusted data processing behaviors by default.**
