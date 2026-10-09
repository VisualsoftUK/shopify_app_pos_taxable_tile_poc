# Secure API Coding: Improper Inventory Management (OWASP API9:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Improper Inventory Management vulnerabilities in APIs. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components (Routing, Controllers, Ingress Handling, Data Serializers, and Versioning architectures), you must actively prevent **Improper Inventory Management**. You have no access to API gateways, service meshes, or system documentation platforms; all mitigations must be implemented directly within the application's code, routing definitions, and environment-aware execution blocks.

## 2. Vulnerability Context at the Application Level

Improper Inventory Management occurs when an application exposes retired, deprecated, unpatched, or developmental versions of API endpoints alongside live production services. Attackers target older API versions because they often lack modern security controls, input validation, or authorization mechanisms. At the code level, this failure typically happens when developers:
* Leave outdated or deprecated endpoint routes active in the routing file (e.g., leaving v1 controllers active when v2 is live).
* Run test, beta, or staging code directly alongside production routes without code-level environment checks.
* Point old API routes to the same underlying data models or live database, allowing unvalidated actions through outdated code paths.
* Fail to programmatically document, deprecate, or drop traffic hitting legacy endpoints.
* Expose undocumented parameters or query structures that bypass newer input filters.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing explicit routing hierarchies, version control structures, and strict data mapping patterns:

### A. Strict Routing Version Control and Decommissioning

* **Rule:** Legacy API versions must be explicitly terminated or decoupled from live systems once deprecated.
* **Action:** Organize routes cleanly by version namespaces (e.g., `/api/v1/`, `/api/v2/`). When an API version is officially retired, you must completely remove or comment out its route definitions from the active production routing engine. Do not leave dead endpoints running "just in case."

### B. Implement a Programmatic Deprecation Strategy

* **Rule:** If a legacy API version must temporarily remain online, it must signal its status and log its usage clearly.
* **Action:** Inject specific deprecation signaling headers into the response middleware for legacy routes. The application layer should automatically include headers such as `Sunset: <date>` and `Deprecation: true` for any traffic hitting designated older versions, and log these hits with high visibility to track legacy clients.

### C. Environmental Separation in Route Registration

* **Rule:** Pre-production code, mocking controllers, and sandbox routes must never compile or register in a production build.
* **Action:** Use strict environment checks (e.g., checking environment variables like `NODE_ENV` or `APP_ENV`) inside the routing configuration file. Wrap test or beta controller registration in conditional statements so they are categorically omitted from the live router if the environment is `production`.

### D. Decouple Database Models via Version-Specific DTOs

* **Rule:** Do not allow outdated API versions to interact directly with modern database structures.
* **Action:** Avoid pointing legacy controllers directly to current data models if the schema has evolved. Use separate Data Transfer Objects (DTOs) or version-specific data mappers to prevent older, unhardened input models from modifying updated database records (Mass Assignment or data corruption through legacy channels).

### E. Block Shadow / Undocumented Parameters

* **Rule:** An API controller must only bind to explicitly declared parameters.
* **Action:** Enforce strict type structures and reject request bodies that contain fields not explicitly documented or defined in the current version's request schema. If an endpoint expects `name` and `email`, configure the validation schema to drop or reject requests containing unlisted parameters like `is_admin` or old parameters like `v1_backup_field`.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API routing or controller logic, run this internal validation across all layers:
- [ ] **Route Cleanliness:** Are there any legacy, unmaintained, or redundant API version routes left active in the file?
- [ ] **Environment Isolation:** Are staging, test, or prototype routes safely wrapped in explicit environment checks to prevent production registration?
- [ ] **Deprecation Signaling:** Do active legacy routes explicitly output deprecation/sunset headers to notify clients?
- [ ] **Data Decoupling:** Are older versions of endpoints isolated from direct database mutations using version-specific filters or mappers?
- [ ] **Parameter Allowlists:** Does the input validation schema strictly discard or reject unexpected fields, protecting against shadow parameters?

**Prioritize security over brevity. All generated API code must demonstrate clean inventory management, strict version boundaries, and environment-aware routing by default.**
