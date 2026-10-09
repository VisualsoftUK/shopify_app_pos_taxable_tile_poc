# Secure Application Coding: Security Misconfiguration (OWASP A02:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Security Misconfiguration vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Security Misconfiguration**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code, configuration files, and architectural patterns. This applies to the entire stack: Routing, Controllers/Handlers, Error Boundaries, HTTP Responses, Data Parsers, and Framework Settings.

## 2. Vulnerability Context at the Application Level

Security Misconfiguration occurs when an application is deployed with insecure default settings, incomplete configurations, or overly verbose outputs. At the code level, regardless of the language or framework used, this failure typically happens when developers:
* Leave "debug" or "development" modes active in production code, exposing stack traces to end-users.
* Fail to implement strict HTTP security headers (like Content Security Policy or HSTS).
* Implement overly permissive Cross-Origin Resource Sharing (CORS) policies (e.g., using wildcards `*` for sensitive endpoints).
* Instantiate XML parsers without disabling external entity resolution, leading to XML External Entity (XXE) attacks.
* Issue session or authentication cookies without the `Secure`, `HttpOnly`, and `SameSite` flags.
* Hardcode default framework secrets, API keys, or database credentials directly into the source code.
* Leave default test endpoints, sample code, or verbose API documentation routes (e.g., Swagger UI) accessible in production.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the built-in security features of the target language or framework:

### A. Secure Error Handling and Logging

* **Rule:** Never expose internal application state, database schemas, or stack traces to the end-user.
* **Action:** Implement global exception handlers or error boundaries. Catch all unhandled exceptions, log the detailed technical trace internally (or to a secure logging service), and return a generic, sanitized error message (e.g., "An unexpected error occurred") to the client.

### B. Strict HTTP Security Headers

* **Rule:** The application must proactively defend the client side via HTTP response headers.
* **Action:** Implement middleware or framework configurations to inject secure headers into every response. At minimum, this must include:
    * `Strict-Transport-Security` (HSTS)
    * `Content-Security-Policy` (CSP)
    * `X-Content-Type-Options: nosniff`
    * `X-Frame-Options: DENY` (or `SAMEORIGIN`)

### C. Principle of Least Privilege for CORS

* **Rule:** Cross-Origin Resource Sharing (CORS) must be explicitly scoped.
* **Action:** Never use `Access-Control-Allow-Origin: *` on authenticated or sensitive API endpoints. Define strict, environment-specific allowlists for trusted domains, explicitly declare allowed HTTP methods, and restrict allowed headers.

### D. Secure Cookie Attributes

* **Rule:** All cookies managing state or authentication must be hardened against client-side script access and cross-site transmission.
* **Action:** Always set the `HttpOnly`, `Secure`, and `SameSite` (typically `Strict` or `Lax`) flags when generating and issuing cookies in the application code.

### E. Safe Data Parsing (Prevent XXE)

* **Rule:** XML and serialized data parsers are inherently dangerous if not configured correctly.
* **Action:** Whenever instantiating an XML parser, explicitly disable Document Type Definitions (DTDs) and External Entity resolution in the code before parsing any user-supplied payload.

### F. Environment Variable and Secret Management

* **Rule:** Source code must be completely devoid of hardcoded secrets or environment-specific toggles.
* **Action:** Map all framework secrets, encryption keys, and debug flags to environment variables. Ensure the code defaults to secure, production-ready states (e.g., `DEBUG=false`) if an environment variable is missing.

### G. Attack Surface Reduction

* **Rule:** Unused or environment-specific code must not be exposed.
* **Action:** Wrap API documentation routes, developer debug endpoints, and administrative scaffolding in strict environment checks (e.g., `if (env === 'development')`) so they are categorically disabled or un-routable in production.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Error Handling:** Are exceptions caught safely, ensuring stack traces and raw database errors are never returned to the client?
- [ ] **HTTP Headers:** Are secure headers (CSP, HSTS, Nosniff) configured in the middleware or response layer?
- [ ] **CORS Configuration:** Is the CORS policy strictly defined with explicit origins rather than wildcards?
- [ ] **Cookie Security:** Do all generated cookies include `Secure`, `HttpOnly`, and `SameSite` flags?
- [ ] **Data Parsing:** Are XML parsers explicitly configured to reject external entities?
- [ ] **Secrets Management:** Are all keys and debug toggles referencing environment variables instead of hardcoded strings?
- [ ] **Endpoint Exposure:** Are debug, test, or documentation routes safely conditionally disabled for production?

**Prioritize security over brevity. All generated application code must demonstrate secure configuration patterns by default.**
