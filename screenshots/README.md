# Screenshots for submission

Add your lab screenshots here, then reference them in `SQL_INJECTION_REPORT.md` or export to PDF.

## Required files

| File | What to capture |
|------|-----------------|
| `01_security_low.png` | DVWA Security page — level set to **Low** |
| `02_basic_sqli.png` | SQL Injection — payload `1' OR '1'='1` — all users shown |
| `03_union_passwords.png` | SQL Injection — `1' UNION SELECT user, password FROM users-- ` |
| `04_blind_boolean.png` | Blind SQLi — `1' AND '1'='1` (exists) and `1' AND '1'='2` (missing) |
| `05_login_bypass.png` | Brute Force — username `admin' --` — welcome message |

## Tips

- Include the browser URL bar (`localhost/DVWA/...`)
- Show the payload in the input field
- Use PNG format for readable text
