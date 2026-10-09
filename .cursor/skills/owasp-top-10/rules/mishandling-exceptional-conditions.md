# Secure Application Coding: Mishandling of Exceptional Conditions (OWASP A10:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent the Mishandling of Exceptional Conditions. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent the **Mishandling of Exceptional Conditions**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code, focusing on robust error handling, transactional integrity, resource cleanup, and "fail-closed" architectural logic.

## 2. Vulnerability Context at the Application Level

Mishandling of Exceptional Conditions (a new category in the 2025 Top 10) occurs when an application fails to anticipate, detect, or respond safely to unusual or error scenarios. At the code level, this failure typically happens when developers:
* Design logic that "fails open" (e.g., granting access if the authentication service times out).
* Leave resources (memory, file locks, database connections) locked or open after an exception is thrown, leading to Denial of Service (DoS) via resource exhaustion.
* Fail to roll back database transactions when an error occurs mid-process, leading to corrupted or inconsistent application state.
* Use empty `catch` blocks (swallowing errors) or catch generic `Exception` base classes without meaningful recovery logic.
* Ignore the return values of functions, assuming external calls or file operations always succeed.
* Leak sensitive system information, stack traces, or logic details through poorly configured error messages.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the target language's error handling and state management features:

### A. Fail Securely (Fail Closed)

* **Rule:** If an operation cannot be completed securely, it must not be completed at all.
* **Action:** Design all security checks, authorization logic, and data validations with a "deny by default" principle. If a timeout, unexpected value, or exception occurs during a critical check, the `catch` block or error handler must explicitly halt the process and deny access. Never allow the application to proceed in a potentially insecure state.

### B. Atomic Transactions and Explicit Rollbacks

* **Rule:** Multi-step data mutations must succeed entirely or fail entirely.
* **Action:** Whenever performing multi-step operations (e.g., financial transfers, multi-table database inserts), wrap the logic in a transactional boundary. If an exception is thrown partway through, the exception handler must explicitly trigger a rollback of all previous changes in that transaction to prevent state corruption.

### C. Guaranteed Resource Cleanup

* **Rule:** Exceptional conditions must not cause resource leaks.
* **Action:** Always utilize `finally` blocks, `defer` statements, `using` directives, or context managers (e.g., `with` in Python) to guarantee the release of file handles, network sockets, memory locks, and database connections, regardless of whether an exception was thrown.

### D. Catch Specific Exceptions Locally

* **Rule:** Error handling must be deliberate and contextual.
* **Action:** Never write empty `catch` blocks. Do not blindly catch generic base exceptions (e.g., `catch (Exception e)`) unless it is the absolute top-level safety net. Catch specific exceptions (e.g., `FileNotFoundException`, `TimeoutException`) at the exact point they occur and implement meaningful, contextual recovery or termination logic.

### E. Verify Return Values and Status Codes

* **Rule:** Never assume an external call or lower-level function succeeded.
* **Action:** If a function returns a status code rather than throwing an exception (common in C, Go, or when calling third-party APIs), explicitly check the return value or HTTP status code. Implement logic to handle unexpected or missing responses safely.

### F. Safe and Generic User Feedback

* **Rule:** Exceptions must never serve as reconnaissance tools for attackers.
* **Action:** Ensure that user-facing error messages generated from caught exceptions are friendly and generic (e.g., "An unexpected error occurred"). Technical details, stack traces, and database syntax must be strictly routed to secure, internal logs.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Fail Closed:** Do exception handlers default to denying access and halting the workflow upon an error?
- [ ] **Transaction Rollbacks:** Are database transactions explicitly rolled back if an error interrupts a multi-step process?
- [ ] **Resource Cleanup:** Are files, sockets, and memory locks reliably released in a `finally` or `defer` block?
- [ ] **Specific Catching:** Are exceptions caught specifically and handled meaningfully, avoiding empty catch blocks?
- [ ] **Return Value Checks:** Are return codes and API status responses explicitly evaluated before the code proceeds?
- [ ] **Information Hiding:** Is the application guaranteed to return sanitized, generic error messages to the user while logging the exact exception internally?

**Prioritize security over brevity. All generated application code must demonstrate resilient, fail-closed exception handling patterns by default.**
