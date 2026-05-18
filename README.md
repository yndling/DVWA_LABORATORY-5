# DVWA_LABORATORY-5

SQL Injection security lab: DVWA exploitation, written report, and **secure code fixes**.

**Live repo:** https://github.com/yndling/DVWA_LABORATORY-5

---

## Assignment deliverables (all included)

| Requirement | File(s) |
|-------------|---------|
| **Report** — vulnerabilities, exploitation, fixes | [`SQL_INJECTION_REPORT.md`](SQL_INJECTION_REPORT.md) |
| **Secure source code** | [`vulnerabilities/sqli/source/secure_version.php`](vulnerabilities/sqli/source/secure_version.php), [`fixed_secure.php`](vulnerabilities/sqli/source/fixed_secure.php), [`sqli_blind/.../secure_version.php`](vulnerabilities/sqli_blind/source/secure_version.php), [`brute/.../secure_version.php`](vulnerabilities/brute/source/secure_version.php), [`login_secure_example.php`](login_secure_example.php) |
| **Vulnerable vs secure comparison** | [`VULNERABLE_VS_SECURE_COMPARISON.md`](VULNERABLE_VS_SECURE_COMPARISON.md) |
| **Lab walkthrough** | [`PRACTICAL_SQL_INJECTION_TESTING.md`](PRACTICAL_SQL_INJECTION_TESTING.md) |
| **Payload reference** | [`SQL_INJECTION_GUIDE.md`](SQL_INJECTION_GUIDE.md) |
| **Submission index** | [`ASSIGNMENT_DELIVERABLES.md`](ASSIGNMENT_DELIVERABLES.md) |
| **Screenshots** | Add PNGs to [`screenshots/`](screenshots/) |

---

## Run locally (XAMPP)

1. Clone this repo into `C:\xampp\htdocs\DVWA` (or symlink).
2. `copy config\config.inc.php.dist config\config.inc.php`
3. Start **Apache** + **MySQL** in XAMPP.
4. Open http://localhost/DVWA/setup.php → **Create / Reset Database**.
5. Login: **admin** / **password**
6. http://localhost/DVWA/security.php → **Low**
7. Test: http://localhost/DVWA/vulnerabilities/sqli/

**Secure login demo:** http://localhost/DVWA/login_secure_example.php

---

## Exploits demonstrated (Low)

| Attack | Payload |
|--------|---------|
| Classic SQLi | `1' OR '1'='1` |
| Union (passwords) | `1' UNION SELECT user, password FROM users-- ` |
| Blind boolean | `1' AND '1'='1` / `1' AND '1'='2` |
| Login bypass | Username: `admin' --` |

---

## Fix summary

Use **prepared statements** (`mysqli_prepare` + `bind_param`) — never concatenate user input into SQL.

See `VULNERABLE_VS_SECURE_COMPARISON.md` for before/after code.

---

## Warning

Educational / local use only. Do not expose DVWA to the internet.
