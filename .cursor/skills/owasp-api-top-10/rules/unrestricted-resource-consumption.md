# Secure API Coding: Unrestricted Resource Consumption (OWASP API4:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Unrestricted Resource Consumption vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components, you must actively prevent **Unrestricted Resource Consumption**. You have no access to API Gateways, Load Balancers, or WAFs; all mitigations must be implemented directly within the application's code by enforcing strict boundaries on pagination, payload sizes, execution timeouts, and computational complexity.

## 2. Vulnerability Context at the Application Level

Unrestricted Resource Consumption occurs when an API does not properly restrict the size or number of resources requested by a client. This leads to Denial of Service (DoS), application crashing, or massive spikes in cloud computing costs. At the code level, this failure typically happens when developers:
* Allow the client to dictate pagination limits without enforcing a hard maximum (e.g., `GET /users?limit=10000000`).
* Fail to configure strict maximum payload sizes for JSON bodies or file uploads.
* Perform computationally expensive tasks (e.g., complex GraphQL queries, image resizing, heavy regex evaluation, password hashing) without limits or timebounds.
* Execute internal database queries or third-party API calls without setting strict timeouts.
* Fail to implement application-level rate limiting or throttling on resource-intensive endpoints.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing native framework limits, timeouts, and resource management features:

### A. Strict Pagination and Record Limits

* **Rule:** The client must never have absolute control over the number of records returned.
* **Action:** For endpoints returning collections of data, always implement a default pagination size (e.g., 20 records) and enforce a strict maximum ceiling (e.g., 100 records). If a client requests `limit=10000`, the application must safely truncate the limit to the maximum ceiling or return a `400 Bad Request`.

### B. Payload and File Size Boundaries

* **Rule:** The application must reject excessively large inputs before processing them.
* **Action:** Explicitly configure the application's body parser and file upload middleware to enforce strict maximum limits on incoming payloads (e.g., limiting JSON bodies to `1MB` and specific file uploads to `5MB`).

### C. Bound Computational Complexity

* **Rule:** Expensive operations must be strictly constrained to prevent CPU/Memory exhaustion.
* **Action:** * **GraphQL:** Implement strict query depth, query cost, and alias limits to prevent nested query attacks.
    * **Regex:** Never use uncontrolled regular expressions on user input (preventing ReDoS). Use safe regex libraries or implement strict execution timeouts.
    * **Processing:** Set maximum dimension and file size boundaries before processing media (e.g., resizing images, parsing massive CSV files).

### D. Application-Level Rate Limiting

* **Rule:** High-cost APIs must be throttled at the application layer.
* **Action:** Implement rate-limiting middleware that restricts the number of requests a client can make within a time window. Rate limits should ideally be bound to the authenticated User ID or Tenant ID, rather than just the IP address, to prevent distributed abuse.

### E. Timeouts for Downstream Calls

* **Rule:** The application must not hang indefinitely waiting for external resources.
* **Action:** Whenever the application executes a database query or makes an HTTP request to a third-party API, explicitly define a strict execution timeout (e.g., 3000ms). Use `catch` blocks to gracefully handle the timeout and fail safely.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code, run this internal validation across all layers:
- [ ] **Pagination Check:** Do all list/collection endpoints enforce a hard maximum limit on returned records?
- [ ] **Payload Limits:** Are request body parsers and upload handlers explicitly configured with maximum size limits?
- [ ] **Complexity Bounds:** Are computationally heavy operations (GraphQL, Regex, media processing) strictly constrained?
- [ ] **Timeouts:** Do all external API calls and database queries feature explicit timeout configurations?
- [ ] **Rate Limiting:** Is application-level throttling applied to resource-intensive or sensitive endpoints?

**Prioritize security over brevity. All generated API code must demonstrate strict resource bounding and defensive consumption patterns by default.**
