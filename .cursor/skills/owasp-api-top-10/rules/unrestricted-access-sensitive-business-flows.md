# Secure API Coding: Unrestricted Access to Sensitive Business Flows (OWASP API6:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Unrestricted Access to Sensitive Business Flows. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components, you must actively prevent **Unrestricted Access to Sensitive Business Flows**. You have no access to API Gateways, WAFs, or dedicated bot-management infrastructure; all mitigations must be implemented directly within the application's code by enforcing strict business-logic limits, anti-automation checks, and state machine validation.

## 2. Vulnerability Context at the Application Level

This vulnerability occurs when an API exposes a sensitive business flow (e.g., buying tickets, posting comments, creating accounts, transferring funds) without preventing automated abuse or excessive usage. Attackers exploit the *legitimate* functionality of the API at scale. At the code level, this failure typically happens when developers:
* Do not apply business-specific rate limits (e.g., allowing a single user to create 1,000 accounts in a minute).
* Allow users to access the final step of a multi-step business flow without verifying they completed the preceding steps.
* Fail to integrate backend validation for human-interaction challenges (e.g., CAPTCHA tokens) on highly abusable endpoints.
* Hardcode assumptions that the client UI will prevent users from clicking a button too many times or submitting too much data.
* Do not track or cap the total business value of operations (e.g., not limiting the number of promotional codes a user can apply).

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing state tracking, strict logical bounds, and application-layer throttling:

### A. Business-Logic Rate Limiting

* **Rule:** Throttling must be tied to the business action and user identity, not just the network IP.
* **Action:** Implement application-level rate limiting specifically tuned to the business flow. Restrict how many times a specific authenticated user, session, or device can perform a sensitive action within a given timeframe (e.g., "maximum 3 checkout attempts per user per hour").

### B. Enforce Strict State Machines

* **Rule:** Multi-step business flows must be enforced chronologically on the server.
* **Action:** Never trust the client to follow the correct sequence of events. If a business flow requires `Step 1 -> Step 2 -> Step 3`, the backend API for `Step 3` must cryptographically or statefully verify that `Step 1` and `Step 2` were successfully completed by that specific user. Reject out-of-order requests.

### C. Human Interaction Verification

* **Rule:** Highly abusable endpoints must verify the presence of a human.
* **Action:** For endpoints prone to automated abuse (e.g., account registration, bulk reservations), integrate backend validation for anti-automation tokens (e.g., reCAPTCHA, hCaptcha, Turnstile). The API must explicitly verify the token's validity with the third-party provider before executing the business logic.

### D. Hardcode Business Constraints and Caps

* **Rule:** Infinite or unbounded business actions are a design flaw.
* **Action:** Explicitly define and enforce absolute maximums within the application logic. Hardcode caps on transactions, cart sizes, promotional code applications, and resource creation to limit the "blast radius" if a flow is targeted by automation.

### E. Session and Context Tracking

* **Rule:** Anomalous shifts in client context during a sensitive flow must be rejected.
* **Action:** Track the user's session context across the business flow. If critical contextual indicators (like the session ID, device fingerprint, or authorization tier) suddenly change between `Step 1` and `Step 2`, the API must invalidate the flow and force the user to restart.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code, run this internal validation across all layers:
- [ ] **Action Throttling:** Are sensitive business flows protected by rate limits tied to the user/session, rather than just IP addresses?
- [ ] **State Enforcement:** Does the backend independently verify that multi-step flows are completed in the exact required order?
- [ ] **Anti-Automation:** Are CAPTCHA or challenge tokens explicitly validated on the server for highly abusable endpoints?
- [ ] **Business Caps:** Are absolute maximums (e.g., max quantities, max actions per day) strictly enforced in the business logic?
- [ ] **Context Validation:** Is the integrity of the user's session maintained and verified throughout the duration of the flow?

**Prioritize security over brevity. All generated API code must demonstrate robust anti-automation and state-enforced business logic by default.**
