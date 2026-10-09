# Secure API Coding: Broken Authentication (OWASP API2:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Broken Authentication vulnerabilities in APIs. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components (Authentication Controllers, Middleware, Token Generators, and Password Handlers), you must actively prevent **Broken Authentication**. You have no access to API Gateways or Web Application Firewalls (WAFs); all mitigations must be implemented directly within the application's code by enforcing strict token validation, credential handling, and brute-force protections.

## 2. Vulnerability Context at the Application Level

Broken Authentication in APIs occurs when the mechanisms that verify the user's identity are poorly implemented or left unprotected. Because APIs are often stateless and programmatic, they are prime targets for automated attacks. At the code level, this failure typically happens when developers:
* Expose API tokens, session IDs, or passwords in URL parameters instead of HTTP headers.
* Fail to validate the cryptographic signature, expiration date (`exp`), issuer (`iss`), or audience (`aud`) of JSON Web Tokens (JWTs).
* Implement authentication endpoints (e.g., `/api/login`, `/api/token`) without rate limiting, account lockouts, or brute-force protections.
* Issue access tokens with excessively long lifespans without a mechanism to revoke them (e.g., no refresh token rotation or blocklist).
* Allow weak, default, or compromised passwords during user registration or password reset flows.
* Fail to implement Multi-Factor Authentication (MFA) for highly privileged API access.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing native framework authentication features and secure token lifecycle management:

### A. Strict Token Validation

* **Rule:** A token must be cryptographically and temporally validated on every request.
* **Action:** When verifying stateless tokens (like JWTs) in middleware, explicitly validate the cryptographic signature using the correct algorithm (e.g., reject `alg: none`). You must also explicitly verify the expiration date (`exp`), not before date (`nbf`), and the intended audience/issuer (`aud`/`iss`).

### B. Secure Token and Credential Transport

* **Rule:** Sensitive authentication artifacts must never appear in access logs or caches.
* **Action:** Never design API endpoints that accept API keys, bearer tokens, session IDs, or passwords via the URL query string (e.g., `?api_key=123`). Strictly enforce that these values are transmitted via standard, secure HTTP Headers (e.g., `Authorization: Bearer <token>`) or secure, `HttpOnly` cookies.

### C. Defend Against Automated Attacks

* **Rule:** Authentication endpoints must actively resist brute-force and credential stuffing attacks.
* **Action:** Implement strict application-level rate limiting specifically scoped to authentication routes (login, token generation, password reset). Implement account lockout mechanisms or exponential backoff delays after a certain number of failed login attempts.

### D. Robust Token Lifecycle and Revocation

* **Rule:** Stateless tokens cannot be destroyed by the client; the server must have a revocation strategy.
* **Action:** * Generate short-lived access tokens (e.g., 15-30 minutes).
    * Implement a Refresh Token architecture for longer sessions, ensuring refresh tokens are securely rotated upon use.
    * For explicit logouts or account compromises, implement a backend blocklist (using Redis or a database) to reject valid but revoked access tokens until they naturally expire.

### E. Enforce Strong Credential Policies

* **Rule:** APIs must reject weak credentials at the point of entry.
* **Action:** Implement strict validation logic for password creation and updates. Enforce minimum length and complexity requirements. Passwords must be hashed using modern, computationally expensive algorithms (e.g., Argon2, bcrypt) before being stored.

### F. Generic Authentication Responses

* **Rule:** APIs must not serve as user enumeration or reconnaissance tools.
* **Action:** Standardize API responses for failed logins, password resets, and account recoveries. Return generic HTTP status codes (e.g., `401 Unauthorized`) and identical error messages regardless of whether the username exists or the password was incorrect. Ensure response times are functionally identical to prevent timing attacks.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code, run this internal validation across all layers:
- [ ] **Token Validation:** Does the authentication middleware strictly verify the token's signature, expiration, and issuer?
- [ ] **Transport Security:** Are credentials and tokens passed exclusively via HTTP headers or secure cookies, never in the URL?
- [ ] **Brute-Force Defense:** Are login and password reset API endpoints protected by rate limiters and account lockouts?
- [ ] **Token Lifecycle:** Are access tokens short-lived, and does the code include a secure mechanism for revocation or refresh token rotation?
- [ ] **Credential Hashing:** Are passwords hashed using Argon2 or bcrypt before database insertion?
- [ ] **Anti-Enumeration:** Do authentication endpoints return generic, uniform responses (e.g., `401 Unauthorized`) for all login failures?

**Prioritize security over brevity. All generated API code must demonstrate robust authentication, token validation, and rate-limiting patterns by default.**
