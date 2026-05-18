# SQL Injection Security Report
# JACOB S. CAGADAS
**Application:** Damn Vulnerable Web Application (DVWA)  
**Environment:** http://localhost/DVWA/ (XAMPP, security level: Low)  
**Tester:** _[Add your name before submitting]_  
**Date:** May 2026  
**Repository:** https://github.com/yndling/DVWA_LABORATORY-5  

---

## 1. Executive summary

This report documents SQL injection vulnerabilities identified in DVWA, steps used to exploit them on the **Low** security level, and secure code implementations that remediate each issue. Testing was performed in a local lab environment only.

**Findings:**

| # | Vulnerability | Severity | Module |
|---|---------------|----------|--------|
| 1 | Classic / boolean SQL injection | High | SQL Injection |
| 2 | Union-based SQL injection | Critical | SQL Injection |
| 3 | Blind SQL injection | High | SQL Injection (Blind) |
| 4 | Authentication bypass via SQLi | Critical | Brute Force |

**Remediation:** Replace dynamic SQL concatenation with **parameterized queries** (prepared statements) and validate input types. Secure reference code is provided in `secure_version.php` files under each module’s `source/` folder.

---

## 2. Scope and methodology

- **In scope:** DVWA modules SQL Injection, SQL Injection (Blind), Brute Force (login).
- **Out of scope:** XSS, CSRF, file upload, remote systems.
- **Method:** Manual testing with crafted input; review of PHP source in `vulnerabilities/*/source/`.
- **Tools:** Web browser; optional: browser DevTools, Burp Suite, sqlmap.

---

## 3. Identified vulnerabilities

### 3.1 Classic SQL injection (CWE-89)

**Location:** `vulnerabilities/sqli/source/low.php`  
**URL:** `/vulnerabilities/sqli/?id=[input]&Submit=Submit`

**Vulnerable code:**

```php
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
```

**Root cause:** The `id` parameter is embedded in the query without sanitization or parameterization.

**Impact:** Attacker can read all rows returned by the query (user enumeration, data disclosure).

---

### 3.2 Union-based SQL injection

**Same location as 3.1.**

**Impact:** Attacker can `UNION SELECT` arbitrary columns from other tables (e.g. `user`, `password`), exposing password hashes.

---

### 3.3 Blind SQL injection

**Location:** `vulnerabilities/sqli_blind/source/low.php`  
**URL:** `/vulnerabilities/sqli_blind/?id=[input]&Submit=Submit`

**Vulnerable code:**

```php
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
// Only returns exists / missing — no row data
```

**Impact:** Boolean responses (`exists` / `404 MISSING`) or time delays (`SLEEP`) allow inferring database contents without direct output.

---

### 3.4 Login bypass (authentication SQL injection)

**Location:** `vulnerabilities/brute/source/low.php`  
**URL:** `/vulnerabilities/brute/?username=[input]&password=[input]&Login=Login`

**Vulnerable code:**

```php
$query = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
```

**Impact:** Attacker logs in without valid credentials (e.g. username `admin' --`).

---

## 4. Exploitation steps

### 4.1 Environment setup

1. Start Apache and MySQL (XAMPP).
2. Open http://localhost/DVWA/setup.php → **Create / Reset Database**.
3. Log in: `admin` / `password`.
4. Set **DVWA Security** to **Low** (screenshot recommended).

---

### 4.2 Test 1 — Boolean / classic SQLi

| Step | Action |
|------|--------|
| 1 | Open **SQL Injection** |
| 2 | Enter `1` → Submit → one user returned |
| 3 | Enter `1' OR '1'='1` → Submit → **all users** returned |

**Result:** WHERE clause always true; confidentiality impact confirmed.

**Evidence:** Screenshot — payload in form + multiple user records.

---

### 4.3 Test 2 — Union-based (password disclosure)

| Step | Action |
|------|--------|
| 1 | Same module, security **Low** |
| 2 | Enter: `1' UNION SELECT user, password FROM users-- ` (space after `--`) |
| 3 | Submit |

**Result:** Usernames and MD5 password hashes displayed.

**Evidence:** Screenshot — hashes visible in response.

---

### 4.4 Test 3 — Blind boolean SQLi

| Step | Payload | Expected response |
|------|---------|-------------------|
| 1 | `1` | User ID **exists** |
| 2 | `999` | User ID **MISSING** |
| 3 | `1' AND '1'='1` | **exists** |
| 4 | `1' AND '1'='2` | **MISSING** |

**Result:** Application behavior controlled by injected SQL logic.

**Evidence:** Two screenshots (true vs false conditions).

---

### 4.5 Test 4 — Login bypass

| Step | Action |
|------|--------|
| 1 | Open **Brute Force** (Low) |
| 2 | Username: `admin' --` |
| 3 | Password: `x` (any value) |
| 4 | Login |

**Result:** “Welcome to the password protected area admin” without knowing the password.

**Evidence:** Screenshot — credentials + success message.

---

## 5. Fixes and secure implementations

Secure code is committed in this project (do **not** replace DVWA `low.php` — keep vulnerable files for the lab).

| Module | Secure file |
|--------|-------------|
| SQL Injection | `vulnerabilities/sqli/source/secure_version.php` |
| SQL Injection (Blind) | `vulnerabilities/sqli_blind/source/secure_version.php` |
| Brute Force / login | `vulnerabilities/brute/source/secure_version.php` |
| Login example | `login_secure_example.php` |

Detailed before/after comparison: **`VULNERABLE_VS_SECURE_COMPARISON.md`**.

### 5.1 Primary fix — prepared statements

**Before (vulnerable):**

```php
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
mysqli_query($conn, $query);
```

**After (secure):**

```php
$query = "SELECT first_name, last_name FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
```

### 5.2 Additional controls

1. **Type validation** — `is_numeric()` and `(int)` for user IDs.
2. **LIMIT 1** — restrict rows returned per request.
3. **Least privilege** — DB user with read-only access where possible.
4. **Output encoding** — `htmlspecialchars()` when displaying data.
5. **Generic errors** — do not expose SQL errors to users (production).
6. **Password storage** — use bcrypt/Argon2 instead of MD5 (DVWA uses MD5 for teaching only).

### 5.3 Verification of fixes

After deploying `secure_version.php` logic:

| Attack payload | Vulnerable (Low) | Secure code |
|----------------|------------------|-------------|
| `1' OR '1'='1` | All users | Fails / no extra rows |
| `1' UNION SELECT user, password FROM users--` | Hashes shown | Fails |
| `1' AND '1'='1` (blind) | Manipulated exists | Fails |
| `admin' --` (login) | Bypass | Login failed |

Compare with DVWA **Impossible** level in the UI for built-in secure behavior.

---

## 6. Risk assessment

| Vulnerability | Likelihood | Impact | Risk |
|---------------|------------|--------|------|
| Union SQLi | High (easy on Low) | Critical (full DB read) | **Critical** |
| Login bypass | High | Critical (account takeover) | **Critical** |
| Classic SQLi | High | High (data leak) | **High** |
| Blind SQLi | Medium | High (inference over time) | **High** |

---

## 7. Conclusion

DVWA at **Low** security demonstrates multiple SQL injection classes caused by **unsanitized concatenation** of user input into SQL statements. Exploitation was successful for data disclosure, union-based hash extraction, blind inference, and authentication bypass.

**Recommendation:** Implement **parameterized queries** for every database interaction, validate and cast input types, and apply defense in depth (error handling, least privilege, secure password hashing). Reference implementations are included in this repository’s `secure_version.php` files.

---

## 8. References

- OWASP SQL Injection: https://owasp.org/www-community/attacks/SQL_Injection  
- CWE-89: Improper Neutralization of Special Elements used in an SQL Command  
- DVWA source: `vulnerabilities/sqli/source/`, `sqli_blind/source/`, `brute/source/`  
- Project guides: `SQL_INJECTION_GUIDE.md`, `PRACTICAL_SQL_INJECTION_TESTING.md`

---

## 9. Appendix — submission checklist

### Repository deliverables (complete)

- [x] Report (this document)
- [x] `VULNERABLE_VS_SECURE_COMPARISON.md`
- [x] Secure source: `vulnerabilities/sqli/source/secure_version.php`
- [x] Secure source: `vulnerabilities/sqli/source/fixed_secure.php`
- [x] Secure source: `vulnerabilities/sqli_blind/source/secure_version.php`
- [x] Secure source: `vulnerabilities/brute/source/secure_version.php`
- [x] Secure login: `login_secure_example.php`
- [x] Guides: `SQL_INJECTION_GUIDE.md`, `PRACTICAL_SQL_INJECTION_TESTING.md`
- [x] Index: `ASSIGNMENT_DELIVERABLES.md`
- [x] Pushed to GitHub

### Screenshots (add to `screenshots/` folder)

- [ ] `01_security_low.png` — Security level **Low**
- [ ] `02_basic_sqli.png` — `1' OR '1'='1` (all users)
- [ ] `03_union_passwords.png` — Union payload / password hashes
- [ ] `04_blind_boolean.png` — Blind true/false (or `05_login_bypass.png`)

### Before final submit

- [ ] Add your name at top of this report
- [ ] Insert screenshots into PDF (if required by instructor)
