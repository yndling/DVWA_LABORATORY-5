# Vulnerable vs Secure Code Comparison

Side-by-side reference for the SQL injection lab deliverable.

| Module | Vulnerable file | Secure file |
|--------|-----------------|-------------|
| SQL Injection | `vulnerabilities/sqli/source/low.php` | `vulnerabilities/sqli/source/secure_version.php` |
| SQL Injection (Blind) | `vulnerabilities/sqli_blind/source/low.php` | `vulnerabilities/sqli_blind/source/secure_version.php` |
| Login bypass | `vulnerabilities/brute/source/low.php` | `vulnerabilities/brute/source/secure_version.php` |
| Login (reference) | `login.php` (DVWA default) | `login_secure_example.php` |

---

## 1. SQL Injection (reflected)

### Vulnerable (`low.php`)

```php
$id = $_REQUEST['id'];
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
```

**Problem:** User input is concatenated inside the SQL string. Payload `1' OR '1'='1` changes query logic.

### Secure (`secure_version.php`)

```php
$id = $_REQUEST['id'];
$query = "SELECT first_name, last_name FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
```

**Fix:** Placeholder `?` keeps input as **data**. Binding `"i"` enforces integer type. Payload is treated as literal text, not SQL.

---

## 2. SQL Injection (Blind)

### Vulnerable (`sqli_blind/source/low.php`)

```php
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
$result = mysqli_query(...);
$exists = (mysqli_num_rows($result) > 0);
```

**Problem:** Attacker learns true/false from page response using `1' AND '1'='1` vs `1' AND '1'='2`, or delays with `SLEEP(5)`.

### Secure (`sqli_blind/source/secure_version.php`)

```php
if (is_numeric($id)) {
    $id = (int) $id;
    $query = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1";
    // ... prepared statement ...
}
```

**Fix:** Validate numeric input, cast to integer, use prepared statement. Injection strings fail validation or are bound safely.

---

## 3. Login bypass (Brute Force)

### Vulnerable (`brute/source/low.php`)

```php
$query = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
```

**Problem:** Username `admin' --` comments out the password check.

### Secure (`brute/source/secure_version.php`)

```php
$query = "SELECT * FROM `users` WHERE user = ? AND password = ? LIMIT 1";
mysqli_stmt_bind_param($stmt, "ss", $user, $pass);
```

**Fix:** Both fields are parameters. `admin' --` is searched as a literal username, not executable SQL.

---

## Defense summary

| Control | Blocks |
|---------|--------|
| Prepared statements | Union, boolean, login bypass |
| Integer validation (`is_numeric` + cast) | String-based blind injection on ID fields |
| `LIMIT 1` | Mass row disclosure via UNION |
| `htmlspecialchars()` on output | XSS from stored/reflected data |
| Generic error messages | Information leakage (Blind low suppresses some errors) |
| CSRF tokens (Impossible level) | Forged login requests |

**Best practice:** Use prepared statements for **all** queries; validate type/range; never build SQL with string concatenation.
