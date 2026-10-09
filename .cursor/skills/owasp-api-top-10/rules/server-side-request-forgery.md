# Secure API Coding: Server-Side Request Forgery (OWASP API7:2023)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Server-Side Request Forgery (SSRF) vulnerabilities in APIs. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing API codebase components that make outbound network requests (e.g., webhook dispatchers, URL preview fetchers, file importers, and integrations with external APIs), you must actively prevent **Server-Side Request Forgery**. You have no access to network firewalls or internal routing configurations; all mitigations must be implemented directly within the application's code.

## 2. Vulnerability Context at the Application Level

SSRF occurs when an API fetches a remote resource without strictly validating the user-supplied URL. An attacker can manipulate the parameter to force the application to send crafted requests to unintended destinations, such as internal cloud metadata endpoints, internal microservices, or loopback addresses (`127.0.0.1`), bypassing network segmentation. At the code level, this failure typically happens when developers:
* Accept a raw URL from the client and immediately pass it to an HTTP client library (e.g., `axios.get(url)`, `fetch(url)`, `requests.get(url)`).
* Rely on weak regex strings to parse or validate URLs instead of built-in URL parsing utilities.
* Implement string-based allowlists or blocklists that are easily bypassed via URL encoding, alternative IP formats (e.g., octal or hexadecimal representations), or DNS rebinding.
* Blindly follow HTTP redirects, allowing a URL that initially looked safe to redirect the application to an internal resource (e.g., `http://metadata.google.internal`).

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing built-in URL parsers and strict data isolation patterns:

### A. Strict Allowlisting of Allowed Domains/Schemes

* **Rule:** An API must only request resources from explicitly trusted external domains and protocols.
* **Action:** Implement a strict, application-layer allowlist. Before initiating a request, parse the user-supplied URL and verify that the protocol is strictly `http` or `https` (never allow `file`, `gopher`, `ftp`, or `dict`) and that the hostname exactly matches a list of pre-approved domains. Reject everything else by default.

### B. Use Native URL Parsing Utilities

* **Rule:** Never use regular expressions or manual string manipulation to parse or validate URLs.
* **Action:** Always use the language or framework's built-in, secure URL parsing object (e.g., `new URL(url)` in JavaScript, `urllib.parse` in Python). Extract the hostname, port, and scheme from this object to perform validations, ensuring the backend checks the exact same components the HTTP library will request.

### C. Detect and Block Private IP Ranges (Network Scoping)

* **Rule:** The application must actively block requests targeting localhost and internal private networks.
* **Action:** If the API must fetch data from generic, user-provided URLs (where a domain allowlist is impossible), resolve the domain to its IP address *before* sending the request. Explicitly validate that the IP address does not fall within loopback, link-local, or private ranges (e.g., `127.0.0.0/8`, `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `169.254.169.254`).

### D. Disable or Validate HTTP Redirects

* **Rule:** Redirects can bypass initial URL boundaries.
* **Action:** Configure the outbound HTTP client library to explicitly disable automatic redirect following (e.g., setting `maxRedirects: 0` or `allowRedirects: false`). If redirects must be followed, intercept each redirect URL and run it through the exact same parsing and validation pipeline (Rules A, B, and C) before the next request is made.

### E. Avoid Passing Raw User Input into Transport Frameworks

* **Rule:** Treat target addresses as configuration, not dynamic user data.
* **Action:** Whenever possible, use an internal identifier (e.g., a dropdown value or internal ID) map instead of a raw URL parameter. For example, if a user selects an integration provider, look up that provider's hardcoded, secure base URL from a configuration dictionary based on their safe selection token.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any API code that triggers outbound network connections, run this internal validation:
- [ ] **Parser Check:** Is the user-supplied URL parsed using a native URL object rather than regular expressions?
- [ ] **Scheme Validation:** Does the code explicitly restrict the protocol to `http` or `https`, blocking all other wrappers?
- [ ] **Destination Restriction:** Is the target domain verified against a strict allowlist, or is its resolved IP explicitly checked to block private/internal network ranges?
- [ ] **Redirect Controls:** Is the HTTP client configured to disable automatic redirects, or is it re-validating each hop manually?
- [ ] **Abstraction Check:** Was the code written to use safe lookup keys instead of letting the user supply a raw, unvalidated URL string?

**Prioritize security over brevity. All generated API code making external requests must demonstrate strict server-side request verification patterns by default.**
