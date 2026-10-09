# Secure API Coding: Security Misconfiguration (OWASP API8:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Security Misconfiguration vulnerabilities in APIs. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components, you must actively prevent **Security Misconfiguration**. You have no access to server infrastructure, API gateways, or external orchestration platforms; all mitigations must be implemented directly within the application's code, configuration arrays, framework initialization scripts, and HTTP response routines.

## 2. Vulnerability Context at the Application Level

Security Misconfiguration in APIs typically manifests when application-level features are left running under unhardened default assumptions, or when verbose errors disclose architectural details. At the code level, regardless of the language or framework used, this failure typically happens when developers:
* Leave debug modes, diagnostic utilities, or environment tracing active in production environments.
* Leave development-only endpoints, prototyping routes, or unauthenticated mocking setups accessible in the live code routing tree.
* Fail to programmatically enforce modern, secure Transport Layer characteristics (such as generating secure cookies or enforcing secure transport flags in application logic).
* Implement overly permissive Cross-Origin Resource Sharing (CORS) configurations, such as reflecting the request origin or using wildcards (`*`) alongside credentials.
* Fail to restrict supported HTTP verbs on endpoints (e.g., leaving a route open to `PUT` or `DELETE` when it only requires `GET`).
* Expose highly detailed application framework stack traces, raw SQL queries, or underlying dependency error dumps directly to API consumers.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the built-in configuration and routing features of the target language or framework:

### A. Environment-Aware Route Mapping

* **Rule:** Testing, debugging, and documentation endpoints must be completely unreachable in production environments.
* **Action:** Wrap the registration of API documentation UI routes (e.g., Swagger, OpenAPI, GraphiQL) and developer utility endpoints in strict environment checks. If the runtime environment flag does not match `development` or `staging`, these routes must not be mounted or evaluated by the routing controller.

### B. Global Clean Error Boundaries

* **Rule:** Internal execution details must be caught and scrubbed at the application boundary before network dispatch.
* **Action:** Implement a centralized catch-all error handling middleware or exception interceptor. For any unhandled runtime error, the middleware must log the detailed trace securely to internal structures and return a generic, sanitized JSON response containing an opaque error tracking code (e.g., `{"error": "Internal Server Error", "tracking_id": "ERR-90812"}`).

### C. Strict Local CORS Policies

* **Rule:** The application must explicitly regulate cross-origin access rules at the boundary.
* **Action:** When defining CORS configurations via middleware code, explicitly define specific, trusted domain origins. Never dynamically reflect the incoming `Origin` header into the response metadata, and categorically forbid the configuration of `Access-Control-Allow-Origin: *` simultaneously with `Access-Control-Allow-Credentials: true`.

### D. Explicit HTTP Verb Restrictions

* **Rule:** Endpoints must reject unneeded and unexpected HTTP methods at the route boundary.
* **Action:** Do not use catch-all route definitions (e.g., `Route::any` or `app.all`) unless it is strictly necessary. Explicitly bind handlers only to the exact required HTTP verbs (`GET`, `POST`, etc.). Ensure the routing framework returns a clean `405 Method Not Allowed` if an unmapped method is attempted.

### E. Defensive App-Layer Header Injection

* **Rule:** The API application layer must proactively send security control headers if infrastructure fails to provide them.
* **Action:** Implement an application-level response middleware to explicitly inject security-hardening headers into all outbound API JSON/XML responses. This must include `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, and tailored `Content-Security-Policy` requirements.

### F. Explicit Secure Cookie Directives

* **Rule:** Session or token delivery cookies generated in code must use explicit security configuration flags.
* **Action:** Whenever outputting token cookies or state handling vectors via code, do not trust default values. Pass explicit configuration parameters forcing `HttpOnly=true`, `Secure=true`, and `SameSite=Lax` (or `Strict`) directly to the cookie factory signature.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API configuration or code, run this internal validation:
- [ ] **Route Inspection:** Are all debugging, schema visualization, and developer utility endpoints conditionally turned off outside of development mode?
- [ ] **Exception Scrubbing:** Does a global error wrapper securely hide stack traces and raw execution faults from the output payload?
- [ ] **CORS Bounds:** Is the origin configuration constrained to a known whitelist instead of a wildcard or dynamic mirroring?
- [ ] **Verb Binding:** Are the routes explicitly limited to designated HTTP verbs rather than open-ended methods?
- [ ] **Hardened Cookies:** Do all programmatic cookie setups explicitly pass secure, http-only, and same-site flags?
- [ ] **Security Headers:** Does the application layer force secure response headers on all outgoing data blocks?

**Prioritize security over brevity. All generated API code must demonstrate secure configuration and environmental profiling patterns by default.**
