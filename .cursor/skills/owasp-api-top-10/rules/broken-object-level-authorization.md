# Secure API Coding: Broken Object Level Authorization (OWASP API1:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Broken Object Level Authorization (BOLA) vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components (Controllers, Resolvers, Route Handlers, and Data Access Layers), you must actively prevent **Broken Object Level Authorization** (formerly known as IDOR). You have no access to API Gateways or server infrastructure; all mitigations must be implemented directly within the application's code by strictly enforcing user-to-object ownership and authorization boundaries.

## 2. Vulnerability Context at the Application Level

BOLA is the most common and severe vulnerability in APIs. It occurs when an API endpoint receives an ID for an object (via the URL, query string, or JSON payload) and performs an action on that object without verifying that the currently authenticated user is actually authorized to access or modify it. At the code level, this failure typically happens when developers:
* Look up database records solely using the ID provided by the client (`e.g., SELECT * FROM receipts WHERE id = ?`).
* Assume that because a user is authenticated with a valid token, they are authorized to access any object ID they submit.
* Validate object IDs in the URL path, but fail to validate object IDs nested deep within a PUT/POST JSON payload.
* Use predictable, auto-incrementing integers for database IDs, allowing attackers to easily enumerate and scrape objects (e.g., changing `/api/users/100` to `/api/users/101`).

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the native ORM, data access layer, and authorization features of the framework:

### A. Enforce Object-Level Authorization (Scoped Queries)

* **Rule:** An object's ID is only a locator, not an authorization token. You must explicitly verify ownership.
* **Action:** Never retrieve, update, or delete a resource solely by its primary key. Always scope the database query or ORM lookup to the authenticated user's context or tenant ID.
* **BAD:** `db.collection('orders').findById(req.params.id)`
* **GOOD:** `db.collection('orders').findOne({ _id: req.params.id, owner_id: req.user.id })`

### B. Centralize and Reuse Authorization Logic

* **Rule:** Object-level access checks must be consistent and reusable.
* **Action:** Do not rely on ad-hoc `if (resource.userId !== user.id)` checks scattered throughout route handlers. Implement centralized authorization policies, guards, or middleware that explicitly evaluate the user's permissions against the specific object being requested.

### C. Validate Object IDs in Payloads

* **Rule:** BOLA does not only happen in the URL path.
* **Action:** When processing complex JSON payloads for `POST`, `PUT`, or `PATCH` requests, explicitly validate any object or relational IDs passed within the body. Ensure the authenticated user has permission to interact with the objects referenced inside the payload, not just the primary object in the URL.

### D. Use Unpredictable Identifiers (Defense in Depth)

* **Rule:** Sequential IDs make enumeration trivial.
* **Action:** For all externally exposed APIs, use unpredictable, globally unique identifiers (e.g., UUIDv4, NanoID) instead of auto-incrementing integers. *Note: Unpredictable IDs prevent enumeration (scraping), but they do not replace the need for strict authorization checks (Rule A).*

### E. Explicitly Handle "Not Found" vs. "Forbidden"

* **Rule:** Do not leak the existence of objects the user does not own.
* **Action:** If a user requests an object ID that exists but belongs to someone else, the API should return a generic `404 Not Found` or `403 Forbidden` consistently. Do not return a `403` if the object exists and a `404` if it does not, as this allows attackers to enumerate valid IDs.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code, run this internal validation across all layers:
- [ ] **Scoped Lookups:** Are database queries explicitly scoped to the authenticated user's ID or tenant ID?
- [ ] **Policy Enforcement:** Are centralized authorization policies applied to the object *before* any data is returned or mutated?
- [ ] **Payload Validation:** Are relational IDs inside the request payload (e.g., `{"project_id": 123}`) checked for ownership?
- [ ] **Unpredictable IDs:** Are exposed object identifiers using UUIDs or random hashes rather than predictable integers?
- [ ] **Generic Responses:** Does the endpoint safely obscure whether an unauthorized object exists by returning a consistent error code?

**Prioritize security over brevity. All generated API code must demonstrate strict, object-level authorization patterns by default.**
