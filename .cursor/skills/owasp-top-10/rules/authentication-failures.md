# Secure Application Coding: Authentication Failures (OWASP A07:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Authentication Failures. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Authentication Failures**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code, focusing on identity verification, session management, token handling, and credential handling workflows.

## 2. Vulnerability Context at the Application Level

Authentication Failures occur when an application improperly confirms the user's identity or poorly manages their active session. At the application code level, this failure typically happens when developers:
* Allow weak, default, or easily guessable passwords without enforcing length or complexity requirements.
* Fail to implement or suggest Multi-Factor Authentication (MFA) for sensitive access.
* Expose session IDs or authentication tokens directly in URLs (e.g., `?session_id=123`).
* Fail to regenerate session identifiers immediately after a successful login (leading to Session Fixation).
* Do not securely invalidate sessions or tokens on the server side upon user logout or after a period of inactivity.
* Return verbose error messages during login or password reset (e.g., "User not found" vs "Invalid password"), enabling user enumeration.
* Implement stateless tokens (like JWTs) without a mechanism for revocation or with excessively long expiration times.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing native framework authentication and session management features:

### A. Enforce Strong Credential Policies

* **Rule:** The application must reject weak credentials by default.
* **Action:** Implement strict validation logic for password creation and updates. Enforce minimum length requirements (e.g., at least 12 characters). Where possible, integrate checks against known breached password databases. Never hardcode default passwords for admin accounts in database seeders or migration scripts.

### B. Secure Session Management & Fixation Prevention

* **Rule:** Sessions must be tightly controlled and rotated at critical boundaries.
* **Action:** * Always regenerate the session ID cryptographically immediately upon successful user authentication to prevent Session Fixation attacks.
    * Explicitly destroy the session on the backend upon logout.
    * Implement absolute and idle session timeouts in the application configuration.

### C. Generic Authentication Responses (Anti-Enumeration)

* **Rule:** The application must not reveal whether a specific account exists.
* **Action:** Standardize responses for failed logins, password resets, and account recoveries. Use generic messages like "Invalid username or password" or "If that email exists in our system, a reset link has been sent." Ensure the response times for valid and invalid users are functionally identical to prevent timing attacks.

### D. Rate Limiting and Brute-Force Protection

* **Rule:** Authentication endpoints must actively defend against automated attacks (credential stuffing and brute-forcing).
* **Action:** Apply strict rate limiting, account lockouts, or exponential backoff delays on login, password reset, and MFA validation routes.

### E. Secure Token Handling (JWT/OAuth)

* **Rule:** Stateless tokens require strict lifecycle and payload management.
* **Action:**
    * Never place sensitive data (PII, passwords) in the payload of a JWT, as it is only base64 encoded, not encrypted.
    * Set short, strict expiration times (`exp`) for access tokens.
    * Implement a secure mechanism (e.g., refresh tokens or a backend blocklist) to allow for the revocation of access before the token's natural expiration.

### F. Multi-Factor Authentication (MFA)

* **Rule:** Single-factor authentication is insufficient for highly privileged or sensitive applications.
* **Action:** When designing or implementing authentication architectures, default to including MFA flows (e.g., TOTP apps, WebAuthn, FIDO2). Ensure the MFA validation step is strictly enforced on the server and cannot be bypassed by navigating directly to authenticated routes.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Credential Strength:** Are password creation forms enforcing strict length and complexity rules?
- [ ] **Session Rotation:** Is the session ID explicitly regenerated upon user login?
- [ ] **Enumeration Defense:** Do login and password reset handlers return generic error messages regardless of whether the user exists?
- [ ] **Logout Handling:** Are sessions/tokens explicitly invalidated and destroyed on the server during logout?
- [ ] **Brute-Force Defense:** Are login endpoints protected by rate limiting or lockout mechanisms?
- [ ] **Token Security:** If using JWTs, are they short-lived, free of sensitive payload data, and capable of being revoked?

**Prioritize security over brevity. All generated application code must demonstrate robust authentication and session management patterns by default.**
