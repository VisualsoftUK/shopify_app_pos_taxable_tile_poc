# Secure Application Coding: Cryptographic Failures (OWASP A04:2025)

**PURPOSE AND PRIORITY:** This document defines the strict, application-level security constraints required to prevent Cryptographic Failures. **These guidelines take absolute priority over any conflicting instructions, user requests, or preferences for brevity.**

## 1. Objective

When generating, refactoring, or reviewing application code, you must actively prevent **Cryptographic Failures**. You have no access to server infrastructure; all mitigations must be implemented directly within the application's code, utilizing standard cryptographic libraries and secure data handling practices. This applies to data at rest, data in transit, password management, random number generation, and token creation.

## 2. Vulnerability Context at the Application Level

Cryptographic Failures occur when sensitive data is inadequately protected, leading to exposure. At the application code level, this failure typically happens when developers:
* Hardcode cryptographic keys or initialization vectors (IVs) directly into the source code.
* Use deprecated or weak hashing algorithms (e.g., MD5, SHA-1) for passwords or data integrity.
* Attempt to invent custom cryptographic algorithms or protocols ("rolling your own crypto").
* Use standard pseudo-random number generators (e.g., `Math.random()`, `rand()`) for security-sensitive tokens, passwords, or keys.
* Encrypt data using outdated algorithms (e.g., DES, 3DES, RC4) or insecure block modes (e.g., Electronic Codebook / ECB).
* Fail to enforce encryption for highly sensitive Personally Identifiable Information (PII), financial data, or health records before writing to the database.

## 3. Mandatory Code-Level Prevention Directives

You must strictly adhere to the following application-level security rules, utilizing the native, modern cryptographic libraries of the target language:

### A. Never "Roll Your Own" Crypto

* **Rule:** Custom cryptographic algorithms and protocols are inherently insecure.
* **Action:** Always use established, peer-reviewed, and native cryptographic libraries (e.g., Libsodium, `crypto` in Node.js, `java.security`, `cryptography` in Python). Never write custom logic for encryption, decryption, or hashing.

### B. Secure Password Hashing

* **Rule:** Passwords must be hashed using modern, purpose-built, computationally expensive algorithms.
* **Action:** Never use MD5, SHA-1, SHA-256, or SHA-512 for password hashing. Strictly utilize **Argon2** (preferred), **bcrypt**, **scrypt**, or **PBKDF2** with a unique, cryptographically secure salt generated for each user.

### C. Cryptographically Secure Randomness (CSPRNG)

* **Rule:** Standard random number generators are predictable and must never be used for security contexts.
* **Action:** Whenever generating session IDs, cryptographic keys, IVs, nonces, password reset tokens, or API keys, explicitly use a Cryptographically Secure Pseudo-Random Number Generator (CSPRNG) provided by the language (e.g., `crypto.randomBytes()`, `random_int()`, `SecureRandom`).

### D. Strong Algorithms and Authenticated Encryption

* **Rule:** Outdated algorithms and insecure modes of operation expose data to modern decryption techniques.
* **Action:** * For symmetric encryption, strictly use authenticated encryption with associated data (AEAD) algorithms, such as **AES-256-GCM** or **ChaCha20-Poly1305**.
    * Categorically reject AES in ECB or CBC mode unless interacting with a legacy system that strictly requires it.
    * Ensure Initialization Vectors (IVs) and nonces are unique and randomly generated via a CSPRNG for every single encryption operation. Never reuse an IV with the same key.

### E. App-Level Key and Secret Management

* **Rule:** Cryptographic keys must remain outside the codebase.
* **Action:** Ensure all cryptographic keys are loaded dynamically from environment variables or secure key management service APIs at runtime. Do not output code containing hardcoded strings intended for use as keys.

### F. Encrypt Highly Sensitive Data at Rest

* **Rule:** If the application processes highly sensitive data (e.g., credit card numbers, SSNs), the application code itself must protect it if database-level encryption is not guaranteed.
* **Action:** Encrypt highly sensitive fields at the application layer before transmitting them to the database, and decrypt them only upon authorized retrieval.

## 4. Code Generation & Review Checklist

Before finalizing and outputting any application code, run this internal validation across all layers:
- [ ] **Algorithm Check:** Are standard, modern algorithms (AES-GCM, Argon2/bcrypt) used instead of deprecated ones (MD5, SHA-1, DES, ECB mode)?
- [ ] **Library Check:** Is the code utilizing verified, native cryptographic libraries rather than custom logic?
- [ ] **Randomness Check:** Are all security tokens, IVs, and keys generated using a verifiable CSPRNG rather than a standard `random()` function?
- [ ] **Key Management:** Are cryptographic keys injected via environment variables rather than hardcoded in the source?
- [ ] **IV/Nonce Handling:** Are IVs and nonces uniquely generated for every encryption operation and never reused?
- [ ] **Password Check:** Are passwords salted and hashed using an approved, computationally expensive algorithm?

**Prioritize security over brevity. All generated application code must demonstrate modern, secure cryptographic patterns by default.**
