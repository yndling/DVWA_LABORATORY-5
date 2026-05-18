# Assignment Deliverables — Complete Package

**Repository:** https://github.com/yndling/DVWA_LABORATORY-5

All required items for the SQL injection project are included in this repo.

---

## 1. Modified source code (secure implementations)

| File | Description |
|------|-------------|
| `vulnerabilities/sqli/source/secure_version.php` | Prepared statements for SQL Injection |
| `vulnerabilities/sqli_blind/source/secure_version.php` | Integer validation + prepared statements (Blind) |
| `vulnerabilities/brute/source/secure_version.php` | Prepared statements for login |
| `login_secure_example.php` | Full secure login page (runnable) |

**Comparison document:** `VULNERABLE_VS_SECURE_COMPARISON.md`

**Vulnerable originals (unchanged for lab):** `low.php` in each module folder

---

## 2. Report (vulnerabilities, exploitation, fixes)

| Document | Purpose |
|----------|---------|
| `SQL_INJECTION_REPORT.md` | **Main report** — findings, steps, fixes, risk, conclusion |
| `SQL_INJECTION_GUIDE.md` | Payload reference |
| `PRACTICAL_SQL_INJECTION_TESTING.md` | Step-by-step lab instructions |

**Before submitting:** Edit your name and date at the top of `SQL_INJECTION_REPORT.md`.

**Screenshots:** Save images in `screenshots/` (see `screenshots/README.md`).

---

## 3. Quick verification

| Test | URL | Payload | Expected |
|------|-----|---------|----------|
| Basic SQLi | `/vulnerabilities/sqli/` | `1' OR '1'='1` | All users |
| Union | `/vulnerabilities/sqli/` | `1' UNION SELECT user, password FROM users-- ` | Hashes |
| Blind | `/vulnerabilities/sqli_blind/` | `1' AND '1'='1` | Exists |
| Login bypass | `/vulnerabilities/brute/` | `admin' --` | Welcome admin |

Security level: **Low** at `/security.php`

---

## 4. Submit to instructor

1. GitHub link: https://github.com/yndling/DVWA_LABORATORY-5  
2. PDF export of `SQL_INJECTION_REPORT.md` (optional: merge screenshots)  
3. Or zip this folder including `screenshots/*.png`

---

## 5. Status checklist

- [x] DVWA lab running (XAMPP)
- [x] Secure source files committed
- [x] Written report and guides
- [x] Pushed to GitHub
- [ ] Add your name/date to report
- [ ] Add screenshot PNGs to `screenshots/`
- [ ] Export final PDF (if required)
