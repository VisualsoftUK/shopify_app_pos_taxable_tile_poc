# Secure Application Coding: Broken Access Control (OWASP A01:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Broken Access Control. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Broken Access Control**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code and architecture. This applies to the entire stack: Routing, Controllers/Handlers, Middleware, ORM/Data Layers, APIs, and any user-facing views or templates.

## 2. Vulnerability Context at the Application Level

Broken Access Control occurs when users can act outside of their intended permissions, accessing or modifying resources that belong to other users, roles, or tenants. At the code level, this failure typically happens when developers:
* Look up resources directly by primary key or identifier without scoping them to the authenticated user or tenant (leading to Insecure Direct Object References / IDOR).
* Rely solely on client-side UI hiding (e.g., conditionally rendering buttons or links) without enforcing the same checks on the server.
* Expose endpoints without attaching authentication or authorization middleware, assuming obscurity provides protection.
* Implement inline, ad-hoc permission checks scattered across handlers instead of centralizing access control logic.
* Return authorization-defaulting-to-allow in request validation layers without performing actual permission verification.
* Issue API tokens or keys with overly broad scopes rather than the minimum required permissions.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the built-in security features of the target language, framework, or middleware stack:

### A. Prevent IDOR via Query Scoping

* **Rule:** Never retrieve a resource solely by its primary key if it belongs to a specific user or tenant.
* **Action:** Always scope data-access queries to the authenticated user's ownership or tenancy. Use ORM relationship methods, query filters, or repository patterns that automatically restrict results to the current user's authorized data set. Never trust a client-supplied ID as sufficient proof of access.

### B. Centralize Authorization Logic

* **Rule:** Access control decisions must be centralized, not scattered across individual handlers.
* **Action:** Use the framework's policy, guard, or role-based access control (RBAC) system to define authorization rules in a single layer. Every state-changing operation (create, update, delete) and every sensitive read must pass through this centralized check before execution. Do not write inline ownership comparisons in controllers or handlers.

### C. Enforce Authorization in Request Validation

* **Rule:** Request validation layers that include an authorization hook must never default to allowing access.
* **Action:** If the framework provides an authorization method within its request validation mechanism, implement strict permission logic there. Never return a blanket "allow" unless the route is explicitly public. Verify that the authenticated user has the specific permission for the specific resource being acted upon.

### D. Deny by Default at the Routing Level

* **Rule:** All routes must require authentication unless explicitly designated as public.
* **Action:** Group protected routes behind authentication middleware. Apply authorization middleware at the route or route-group level so that unauthenticated or unauthorized requests are rejected before reaching handler logic. Prefer a deny-by-default architecture where new routes inherit protection automatically.

### E. Enforce Least-Privilege API Token Scopes

* **Rule:** API tokens and keys must carry the minimum permissions necessary.
* **Action:** When creating API tokens, assign specific, narrowly scoped abilities or permissions. Verify these scopes on every request — do not assume a valid token implies full access. Reject requests where the token lacks the required scope for the operation.

### F. Server-Side Enforcement for All UI-Hidden Elements

* **Rule:** Client-side visibility controls are not security controls.
* **Action:** Every action that is conditionally hidden in the UI (buttons, links, menu items) must have a corresponding server-side authorization check on the backing endpoint. Never rely on the frontend alone to prevent unauthorized access.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Routing:** Are sensitive routes protected by authentication and authorization middleware?
- [ ] **Authorization:** Is a centralized authorization mechanism (policies, guards, RBAC) used before performing CRUD operations?
- [ ] **Query Scoping:** Are queries for user-owned or tenant-scoped resources filtered via relationships or scopes rather than global lookups by ID?
- [ ] **Request Validation:** Is any framework-level authorization hook actively verifying permissions rather than defaulting to allow?
- [ ] **UI Enforcement:** Are actions hidden in the frontend also protected by server-side checks on the corresponding endpoints?
- [ ] **Token Scopes:** Are API tokens issued with minimal scopes, and are those scopes verified on every request?

**Prioritize security over brevity. All generated application code must demonstrate strict access control enforcement patterns by default.**
