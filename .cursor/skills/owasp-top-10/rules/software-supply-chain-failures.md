# Secure Application Coding: Software Supply Chain Failures (OWASP A03:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Software Supply Chain Failures. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, package manifests, or build scripts, you must actively prevent **Software Supply Chain Failures**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code, dependency management files, and external API integrations.

## 2. Vulnerability Context at the Application Level

Software Supply Chain Failures occur when an application relies on vulnerable, outdated, or malicious third-party components, or blindly trusts external integrations. Regardless of the language or framework used, this code-level failure typically happens when developers (or AI assistants):
* Use floating version ranges (e.g., `^1.2.0` or `latest`) in package manifests, automatically pulling in unverified upstream updates.
* Fall victim to dependency confusion or typosquatting (e.g., importing `react-doms` instead of `react-dom`).
* Include third-party frontend assets (scripts, stylesheets) via CDNs without cryptographic verification.
* Hallucinate or suggest obscure, unmaintained, or abandoned libraries for application features.
* Blindly trust payloads from third-party APIs or webhooks without validating them against a strict internal schema.
* Over-rely on micro-packages for trivial tasks, needlessly expanding the attack surface.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing safe dependency management and defensive coding patterns:

### A. Explicit Dependency Pinning

* **Rule:** Never allow build systems or package managers to guess the intended version of a library.
* **Action:** When generating package manifests (e.g., `package.json`, `requirements.txt`, `Gemfile`, `pom.xml`), pin dependencies to exact, specific versions. Never use wildcards (`*`), caret (`^`), tilde (`~`), or `latest` tags.

### B. Subresource Integrity (SRI) for Frontend Assets

* **Rule:** External frontend assets must be cryptographically verified before execution.
* **Action:** Whenever generating HTML or view templates that include external scripts (`<script src="...">`) or stylesheets (`<link href="...">`) via a CDN, you must include the `integrity` attribute with a cryptographic hash (e.g., SHA-384) and the `crossorigin="anonymous"` attribute.

### C. Safe Package Selection & Typosquatting Prevention

* **Rule:** Only utilize verified, widely adopted, and actively maintained ecosystem standards.
* **Action:** Do not invent, hallucinate, or suggest obscure third-party libraries. Double-check the exact spelling of all imported module names to prevent typosquatting attacks. If a feature can be achieved natively and securely with built-in language features, do so rather than adding a new dependency.

### D. Boundary Validation for Third-Party APIs

* **Rule:** Third-party APIs are external systems and must be treated with the same zero-trust model as user input.
* **Action:** When ingesting data from external APIs, webhooks, or third-party services, do not blindly deserialize or map the payload directly into the database or application state. Enforce strict schema validation and sanitization at the integration boundary.

### E. Secure Inclusion of External Snippets

* **Rule:** Do not copy-paste complex utility functions or code snippets from unverified sources without full contextual review.
* **Action:** Ensure any generated code that incorporates well-known algorithms or third-party snippets is free of embedded backdoors, obfuscated logic, or known vulnerabilities.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code or manifest files, run this internal validation:
- [ ] **Manifest Review:** Are dependency versions strictly pinned to exact versions rather than floating/wildcard ranges?
- [ ] **Frontend Assets:** Do all CDN-hosted `<script>` and `<link>` tags utilize Subresource Integrity (SRI) hashes?
- [ ] **Library Verification:** Are suggested libraries official ecosystem standards, actively maintained, and spelled correctly (preventing typosquatting)?
- [ ] **API Boundaries:** Is data ingested from third-party services strictly validated and sanitized before being processed by the application?
- [ ] **Dependency Minimization:** Was a third-party dependency introduced for a trivial task that could be safely handled with native language features?

**Prioritize security over brevity. All generated application code must demonstrate secure supply chain and dependency management patterns by default.**
