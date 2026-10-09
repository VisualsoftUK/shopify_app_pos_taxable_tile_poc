# Secure Application Coding: Injection (OWASP A05:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Injection vulnerabilities. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Injection**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code and architecture. This applies to the entire stack: Routing, Controllers/Handlers, ORM/Data Layers, OS Command Execution, and any integration with external interpreters (SQL, NoSQL, LDAP, XML, etc.).

## 2. Vulnerability Context at the Application Level

Injection occurs when untrusted user data is sent to an interpreter as part of a command or query. The interpreter is tricked into executing unintended commands or accessing data without proper authorization. At the code level, this failure typically happens when developers:
* Concatenate untrusted data directly into SQL, NoSQL, or LDAP query strings.
* Pass raw, unvalidated user input directly into operating system commands (e.g., via `system()`, `exec()`, or `popen()`).
* Use Object-Relational Mapping (ORM) tools unsafely by passing user input into "raw" query methods.
* Evaluate user-supplied strings dynamically as code (e.g., using `eval()`, `setTimeout()` with strings, or insecure deserialization).
* Fail to implement strict, positive server-side input validation (allowlisting) at the request boundary.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the built-in security features of the target language, framework, or database driver:

### A. Mandate Parameterized Queries (Prepared Statements)

* **Rule:** User input must never be concatenated into database queries.
* **Action:** For all SQL and NoSQL database interactions, strictly enforce the use of parameterized queries, prepared statements, or secure ORM/ODM abstraction methods. The database driver must treat user input strictly as data, never as executable syntax.

### B. Safe ORM/ODM Usage

* **Rule:** ORMs are not inherently immune to injection if used improperly.
* **Action:** Never pass untrusted user input directly into an ORM's "raw" or "whereRaw" query builder methods. Always use the built-in, type-safe filtering and scoping methods provided by the ORM.

### C. Eliminate OS Command Injection

* **Rule:** Executing operating system commands from application code is highly dangerous and should be avoided.
* **Action:** If an OS command must be executed, never concatenate user input into a single command string. Use language-specific safe APIs that execute commands by passing the executable and its arguments as a discrete array/list (e.g., `child_process.execFile()` in Node.js, `subprocess.run([...])` in Python).

### D. Strict Input Validation (Allowlisting)

* **Rule:** All external input is untrusted and must be validated before processing.
* **Action:** Implement positive (allowlist) server-side input validation at the application boundary. Validate the exact expected type, length, format, and range of the data. Reject any payload that does not strictly conform to the expected schema.

### E. Prevent Dynamic Code Execution

* **Rule:** Application code must never execute strings provided by users.
* **Action:** Categorically forbid the use of dynamic execution functions like `eval()`, `exec()`, or `Function()` constructors with user-supplied data. Find architectural alternatives that do not rely on dynamic code evaluation.

### F. Safe Query Architecture (LIMIT and Context)

* **Rule:** Limit the blast radius of a potential injection attack.
* **Action:** Always append `LIMIT` clauses to queries that return sets of data to prevent mass data exfiltration. When using interpreters that do not support parameterized interfaces, strictly apply context-aware escaping libraries specific to that interpreter before inserting data.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Database Queries:** Are all SQL/NoSQL operations using parameterized queries or safe ORM methods instead of string concatenation?
- [ ] **OS Commands:** If calling the OS, are arguments passed securely via arrays/lists rather than raw string execution?
- [ ] **Input Boundary:** Is strict allowlist validation enforced for all incoming request payloads, headers, and parameters?
- [ ] **Code Execution:** Are `eval()` and similar dynamic execution functions completely absent when handling user input?
- [ ] **ORM Methods:** Are "raw" query builder methods strictly avoiding direct insertion of user variables?

**Prioritize security over brevity. All generated application code must demonstrate secure data handling and injection prevention patterns by default.**
