# Secure API Coding: Broken Object Property Level Authorization (OWASP API3:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Broken Object Property Level Authorization (BOPLA) vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components (Controllers, ORM models, Serializers, and Payload Validators), you must actively prevent **Broken Object Property Level Authorization**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code by strictly controlling which object properties can be read or modified by the user.

## 2. Vulnerability Context at the Application Level

Broken Object Property Level Authorization combines two previously distinct vulnerabilities: **Excessive Data Exposure** (unauthorized reading of properties) and **Mass Assignment** (unauthorized writing of properties). It occurs when a user has permission to access an object, but the API fails to restrict access to specific *properties* of that object based on the user's role. At the code level, this failure typically happens when developers:
* Return entire database records or ORM models directly in the API response (e.g., `return user;`), exposing sensitive properties like `password_hash`, `ssn`, or `internal_notes`, and relying on the frontend client to hide them.
* Blindly bind or merge incoming JSON payloads directly into database records (e.g., `user.update(req.body);`), allowing an attacker to inject and overwrite sensitive properties like `is_admin: true` or `account_balance: 9999`.
* Fail to implement different property-level permissions for different roles (e.g., a standard user can view their profile, but only an HR admin should view their `salary` property).

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing Data Transfer Objects (DTOs), strict serialization, and payload allowlisting:

### A. Prevent Mass Assignment (Write Restrictions)

* **Rule:** Never blindly merge client input with internal data structures or database records.
* **Action:** Strictly define allowlists for bindable properties. When accepting data for object creation or updates, explicitly extract only the permitted fields from the request payload. Reject or ignore any unexpected or unauthorized properties.
* **BAD:** `user.update(request.body)`
* **GOOD:** `user.update({ email: request.body.email, name: request.body.name })`

### B. Prevent Excessive Data Exposure via DTOs (Read Restrictions)

* **Rule:** Hiding data on the frontend does not secure the backend. The API must never transmit sensitive properties to the client.
* **Action:** Never serialize raw ORM objects or database rows directly into API responses. Strictly utilize Data Transfer Objects (DTOs), resource mappers, or serializers to explicitly map and define exactly which fields are exposed to the client.

### C. Role-Based Property Filtering

* **Rule:** Access to object properties must be evaluated based on the user's explicit permissions.
* **Action:** If an API endpoint returns or updates an object that contains mixed-sensitivity properties, implement logic to conditionally filter those properties based on the requester's role. (e.g., If `user.role != 'admin'`, exclude the `clearance_level` property from the DTO and reject any attempts to update it).

### D. Strict Schema Validation

* **Rule:** The API must reject unstructured or unexpected payloads at the boundary.
* **Action:** Implement strict schema validation (e.g., using JSON Schema, Zod, Joi, or framework-specific validators) for all incoming requests. Configure the validator to explicitly reject the request if it contains extraneous properties not defined in the schema.

### E. Avoid Generic "Update All" Endpoints

* **Rule:** Broad, generic endpoints increase the attack surface for property manipulation.
* **Action:** Favor specific, intention-based endpoints (e.g., `PUT /api/users/123/email`, `POST /api/users/123/reset-password`) over a single generic endpoint (`PUT /api/users/123`) that accepts a massive payload to update any part of the object.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code, run this internal validation across all layers:
- [ ] **Mass Assignment Check:** Is incoming data explicitly allowlisted or safely destructured before mutating database records?
- [ ] **Data Exposure Check:** Are raw ORM/Database objects prevented from being returned directly? Are DTOs or serializers actively used?
- [ ] **Schema Validation:** Does the payload validator strictly reject requests containing unexpected or undefined properties?
- [ ] **Property-Level Auth:** Are conditional checks in place for properties that should only be viewed or modified by specific roles (e.g., admins)?
- [ ] **Client-Side Trust:** Does the backend completely manage property filtering rather than relying on the UI to hide data?

**Prioritize security over brevity. All generated API code must demonstrate strict property-level data handling and serialization patterns by default.**
