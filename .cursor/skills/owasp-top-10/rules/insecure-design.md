# Secure Application Coding: Insecure Design (OWASP A06:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Insecure Design vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code and architecture, you must actively prevent **Insecure Design**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code, focusing on robust business logic, state management, threat modeling concepts, and secure architectural patterns.

## 2. Vulnerability Context at the Application Level

Insecure Design refers to risks related to architectural flaws and missing or ineffective control designs. Unlike implementation bugs (where a good design is coded poorly), an insecure design cannot be fixed by a perfect implementation. At the application code level, this failure typically happens when developers:
* Fail to enforce business logic limits (e.g., allowing a user to order negative quantities or skip payment steps).
* Lack anti-automation or rate-limiting on sensitive endpoints (e.g., login, password reset, purchasing flows).
* Design insecure credential recovery workflows (e.g., using predictable "security questions" instead of out-of-band links).
* Implicitly trust data passed between internal application components without re-validating at trust boundaries.
* Design systems that fail "open" (granting access upon error) rather than failing "closed" (securely).
* Collect and store highly sensitive data (data hoarding) that the business logic does not strictly require.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing secure design patterns and state management:

### A. Enforce Strict Business Logic and State Machines

* **Rule:** Multi-step processes must cryptographically or server-side enforce the sequential execution of steps.
* **Action:** Never rely on the client frontend to enforce the order of operations. Use server-side state machines or session-backed tracking to ensure users cannot skip mandatory steps (e.g., skipping the "checkout" API call and going straight to "receipt" API call). Validate all business limits (e.g., quantities, minimums/maximums) on the backend.

### B. Implement Application-Layer Rate Limiting

* **Rule:** Sensitive and resource-intensive endpoints must be protected against automated abuse.
* **Action:** Apply application-level rate limiting middleware (e.g., using Redis or framework-native limiters) to critical endpoints, especially authentication, account creation, password recovery, and transactional routes.

### C. Secure Authentication and Recovery Flows

* **Rule:** Account recovery must be as secure as primary authentication.
* **Action:** Never generate code that uses Knowledge-Based Authentication (KBA) such as "security questions" (e.g., "What is your mother's maiden name?"). Implement secure, time-limited, single-use, out-of-band recovery links (e.g., sent via email or SMS) utilizing Cryptographically Secure Pseudo-Random Number Generators (CSPRNG).

### D. Re-Validate at Trust Boundaries

* **Rule:** Data moving between internal subsystems must not be implicitly trusted simply because it originated internally.
* **Action:** Define clear trust boundaries. Even if data comes from another internal microservice or background job, validate its schema and authorization context before processing it in the current module.

### E. Design for Data Minimization

* **Rule:** The best way to protect data is to not collect or store it.
* **Action:** When designing data models or API ingestion points, only accept and persist the exact fields strictly required by the immediate business logic. Strip out excess PII or sensitive data before it reaches the persistence layer.

### F. Fail Securely (Fail Closed)

* **Rule:** Unexpected exceptions or logical errors must default to the highest security posture.
* **Action:** Ensure that `catch` blocks and error boundaries default to denying access, rejecting transactions, or logging the user out. Never allow an error state to bypass an authorization check or approve a workflow.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application architecture or code, run this internal validation:
- [ ] **State Enforcement:** Are multi-step business workflows explicitly enforcing state on the server, preventing step-skipping?
- [ ] **Rate Limiting:** Are auth, recovery, and transactional endpoints protected by code-level rate limiters?
- [ ] **Account Recovery:** Is credential recovery using secure, single-use tokens instead of predictable security questions?
- [ ] **Business Constraints:** Are numerical limits (e.g., no negative item quantities, max purchase limits) strictly validated on the backend?
- [ ] **Fail-Safe Defaults:** Do error handlers and exceptions safely default to denying access or halting the transaction?
- [ ] **Data Minimization:** Is the data model designed to store only the absolute minimum required sensitive information?

**Prioritize security over brevity. All generated application code must demonstrate secure architectural design patterns by default.**
