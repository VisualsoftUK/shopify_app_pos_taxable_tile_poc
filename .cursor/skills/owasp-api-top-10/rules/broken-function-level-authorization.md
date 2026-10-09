# Secure API Coding: Broken Function Level Authorization (OWASP API5:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Broken Function Level Authorization (BFLA) vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components (Routing Layers, Controllers, Middleware, and Decorators), you must actively prevent **Broken Function Level Authorization**. You have no access to API Gateways or external IAM services; all mitigations must be implemented directly within the application's code by enforcing strict role-based (RBAC) or attribute-based (ABAC) access controls on every single API endpoint.

## 2. Vulnerability Context at the Application Level

Broken Function Level Authorization occurs when an API fails to adequately verify if the requesting user possesses the correct hierarchical role or permissions to execute a specific function or access a specific endpoint. At the code level, this failure typically happens when developers:
* Rely on the frontend UI to hide administrative actions (like a "Delete User" button) but leave the corresponding backend API endpoint (`DELETE /api/users/{id}`) accessible to any authenticated user.
* Utilize an "allow by default" routing architecture, requiring developers to explicitly remember to add security to new endpoints.
* Protect the endpoint path but fail to differentiate between HTTP methods (e.g., allowing all users to `GET /api/documents`, but forgetting to restrict `DELETE /api/documents` to admins).
* Scatter complex, inconsistent role-checking logic throughout individual controller functions rather than using centralized middleware.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the native routing and middleware capabilities of the framework:

### A. Deny by Default Architecture

* **Rule:** Access to any API function must be explicitly granted, never assumed.
* **Action:** Configure the application's routing mechanism to deny access by default. Unless an endpoint is explicitly decorated or registered as public, it must require both authentication and a specific authorization baseline.

### B. Centralize Function Authorization

* **Rule:** Role and permission validation must happen at the routing boundary.
* **Action:** Never rely on inline `if (user.role == 'admin')` statements scattered deep within business logic. Strictly utilize centralized authorization middleware, guards, or decorators applied directly to the route definitions or controller classes.

### C. Explicit HTTP Method Constraints

* **Rule:** Permissions must be scoped to the specific HTTP verb, not just the URL path.
* **Action:** When defining routes, explicitly bind the required permission to the HTTP method. Ensure that state-changing methods (`POST`, `PUT`, `PATCH`, `DELETE`) require stricter role validations than read-only methods (`GET`), even if they share the same base URL.

### D. Secure Administrative Grouping

* **Rule:** Administrative and highly privileged functions must be structurally isolated.
* **Action:** Group all administrative API endpoints under a distinct namespace or route group (e.g., `/api/admin/v1/`). Apply overarching, strict role-validation middleware to the entire group definition to ensure no individual administrative route is accidentally left unprotected.

### E. Zero Trust in the Presentation Layer

* **Rule:** The API must act as the absolute source of truth for authorization.
* **Action:** Never assume that because a feature is disabled or hidden in the client-side application, the user cannot execute the function. The backend API code must independently and rigorously verify the user's role before executing any business logic.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code, run this internal validation across all layers:
- [ ] **Deny by Default:** Are new endpoints automatically protected unless explicitly marked as public?
- [ ] **Boundary Enforcement:** Is role validation handled via middleware/decorators rather than buried in controller business logic?
- [ ] **Method-Level Checks:** Are `PUT`/`POST`/`DELETE` methods explicitly restricted by role, even if the `GET` method is broadly accessible?
- [ ] **Admin Isolation:** Are administrative endpoints grouped logically and protected by a blanket administrative authorization policy?
- [ ] **Backend Independence:** Is the API actively validating permissions rather than trusting the frontend to restrict access?

**Prioritize security over brevity. All generated API code must demonstrate strict, boundary-enforced function-level authorization patterns by default.**
