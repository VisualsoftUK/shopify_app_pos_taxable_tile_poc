# Secure Application Coding: Software or Data Integrity Failures (OWASP A08:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Software or Data Integrity Failures. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Software or Data Integrity Failures**. You have no access to server infrastructure or CI/CD pipelines; all mitigations must be implemented directly within the application's code. This applies to data deserialization, webhook handling, dynamic module/plugin loading, and processing unverified payloads.

## 2. Vulnerability Context at the Application Level

Software or Data Integrity Failures occur when code and infrastructure do not protect against integrity violations. At the application code level, this failure typically happens when developers:
* Use insecure native serialization mechanisms (e.g., Python's `pickle`, Java's `ObjectInputStream`, PHP's `unserialize()`, .NET's `BinaryFormatter`) on untrusted data.
* Blindly accept and execute serialized objects that contain malicious executable code or state manipulations.
* Process incoming webhooks, server-to-server payloads, or messages from message brokers without verifying their cryptographic signature.
* Dynamically load plugins, scripts, or application updates without verifying a checksum or digital signature.
* Rely on client-side state (like cookies or hidden fields) that hasn't been cryptographically signed to prevent tampering.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing safe data handling and integrity verification patterns:

### A. Ban Insecure Native Deserialization

* **Rule:** Native object deserialization mechanisms are inherently dangerous when exposed to untrusted input.
* **Action:** Never use native serialization formats that allow for arbitrary object instantiation or code execution (e.g., `pickle`, `unserialize()`). Strictly utilize data-only serialization formats like JSON, Protocol Buffers, or simple XML (with XXE protections enabled) for transferring state or receiving input.

### B. Cryptographic Verification of Payloads

* **Rule:** Unsigned data from external sources cannot be trusted.
* **Action:** When receiving webhooks, API payloads from third parties, or messages from external queues, explicitly verify the data's integrity using Digital Signatures or Hash-Based Message Authentication Codes (HMAC). Reject any payload that lacks a valid signature or fails verification.

### C. Protect Client-Side State Integrity

* **Rule:** If state must be stored on the client, it must be tamper-proof.
* **Action:** When utilizing cookies, tokens, or hidden form fields to maintain state, always apply a cryptographic signature (e.g., signed cookies, JWTs with a strong HMAC/RSA signature) to ensure the user cannot maliciously alter the data.

### D. Strict Schema Validation

* **Rule:** Even safe serialization formats (like JSON) must be structurally validated.
* **Action:** Before parsing and processing incoming data structures, apply strict schema validation (e.g., JSON Schema, Data Transfer Objects with validation decorators). Ensure the incoming data strictly matches the expected types, properties, and constraints.

### E. Verify Dynamic Modules and Plugins

* **Rule:** Dynamically loaded code must have guaranteed provenance and integrity.
* **Action:** If the application architecture involves downloading or dynamically loading plugins, scripts, or binary modules at runtime, the code must calculate and verify the cryptographic hash (e.g., SHA-256) or digital signature of the module against a trusted source before execution.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Deserialization:** Are native object deserializers completely avoided for untrusted data in favor of safe formats like JSON?
- [ ] **Payload Integrity:** Are incoming webhooks and external payloads cryptographically verified (HMAC/Signatures) before processing?
- [ ] **State Tampering:** Is any client-side state (cookies, hidden fields) protected against tampering via cryptographic signatures?
- [ ] **Data Validation:** Are incoming serialized payloads strictly validated against a predefined schema?
- [ ] **Code Loading:** If dynamically loading modules or updates, is the integrity of the downloaded asset verified prior to execution?

**Prioritize security over brevity. All generated application code must demonstrate secure data integrity and deserialization patterns by default.**
