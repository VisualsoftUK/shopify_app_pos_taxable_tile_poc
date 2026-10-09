# Secure Application Coding: Security Logging and Alerting Failures (OWASP A09:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Security Logging and Alerting Failures. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Security Logging and Alerting Failures**. You have no access to server infrastructure or external SIEM tools; all mitigations must be implemented directly within the application's code via robust logging frameworks, structured log formats, data redaction, and application-level anomaly emission.

## 2. Vulnerability Context at the Application Level

Security Logging and Alerting Failures occur when an application fails to record critical security events, making incident response and breach detection impossible. At the application code level, this failure typically happens when developers:
* Fail to log authentication events (e.g., failed logins, password resets, account lockouts).
* Fail to log authorization failures (e.g., a user attempting to access another user's data or an admin panel).
* Log highly sensitive data in plain text, such as passwords, session tokens, or Personally Identifiable Information (PII).
* Use unstructured, inconsistent print statements instead of a dedicated, parseable logging framework.
* Fail to include sufficient context in the logs (e.g., missing user IDs, timestamps, or IP addresses).
* Write user-supplied input directly to logs without sanitization, allowing for Log Forging or CRLF injection.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the target framework's native logging libraries:

### A. Comprehensive Security Event Logging

* **Rule:** All security-critical actions and failures must generate a log entry.
* **Action:** Ensure the application code explicitly logs the following events:
    * Authentication successes, failures, and lockouts.
    * Password changes, account recoveries, and MFA modifications.
    * Authorization and access control failures (e.g., IDOR attempts, role bypasses).
    * Server-side input validation failures.
    * Unhandled exceptions and critical application errors.

### B. Strict Log Sanitization and Redaction

* **Rule:** Logs must never become a secondary data breach vector.
* **Action:** Never log raw passwords, active session identifiers, API keys, full credit card numbers, or unnecessary PII. Implement redaction or masking filters within the logging utility to strip or hash sensitive parameters before they are written to the log stream.

### C. Structured and Contextual Logging

* **Rule:** Logs must be machine-readable and forensically useful.
* **Action:** Never use arbitrary string concatenation (e.g., `print("User " + user + " failed to login")`). Strictly utilize structured logging formats (e.g., JSON) via a robust logging library. Every security log must include context: a standardized timestamp, the authenticated user's ID (if available), the attempted action, and the outcome.

### D. Log Injection Prevention

* **Rule:** Untrusted user input cannot be blindly written to log files.
* **Action:** Sanitize or encode any user-supplied input (like usernames, User-Agent strings, or URL parameters) before passing it to the logging function. This prevents attackers from injecting newline characters (CRLF) to forge fake log entries or hide their tracks.

### E. High-Signal Anomaly Triggers

* **Rule:** The application must actively signal when under attack.
* **Action:** For severe security violations (e.g., repeated access control failures from the same user, mass assignment attempts, or repeated JWT validation failures), write application logic that elevates the log severity to `CRITICAL` or `FATAL`, or emits a specific event payload that downstream alerting systems can easily catch.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Event Coverage:** Are all authentication, authorization, and validation failures explicitly logged?
- [ ] **Data Sanitization:** Is the logging implementation guaranteed to filter out passwords, session IDs, and sensitive PII?
- [ ] **Structured Format:** Are logs generated in a structured format (e.g., JSON) rather than concatenated strings?
- [ ] **Log Injection Defense:** Is user input encoded or sanitized before being written to the log output?
- [ ] **Context Included:** Do the log entries contain sufficient forensic context (User ID, Action, Outcome)?
- [ ] **Alerting Hooks:** Are critical security violations logged at an elevated severity level to trigger external alerts?

**Prioritize security over brevity. All generated application code must demonstrate secure, comprehensive, and sanitized logging patterns by default.**
