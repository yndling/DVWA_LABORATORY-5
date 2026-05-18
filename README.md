# DVWA_LABORATORY-5

SQL Injection testing lab based on [Damn Vulnerable Web Application (DVWA)](https://github.com/digininja/DVWA), with documentation, secure code fixes, and lab reports.

## Contents

- `SQL_INJECTION_GUIDE.md` — payloads and techniques
- `PRACTICAL_SQL_INJECTION_TESTING.md` — step-by-step lab
- `SQL_INJECTION_REPORT.md` — vulnerabilities, exploitation, fixes
- `VULNERABLE_VS_SECURE_COMPARISON.md` — vulnerable vs secure code
- `vulnerabilities/*/source/secure_version.php` — remediated implementations

## Setup (XAMPP)

1. Copy or link this folder to `C:\xampp\htdocs\DVWA`
2. Copy `config/config.inc.php.dist` to `config/config.inc.php`
3. Start Apache and MySQL, open http://localhost/DVWA/setup.php
4. Create database, login: `admin` / `password`
5. Set security level to **Low** for SQLi practice

## Default credentials

- **DVWA login:** `admin` / `password`

## Warning

For local/educational use only. Do not deploy to the internet.
