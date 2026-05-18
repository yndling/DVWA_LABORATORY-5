<?php
/**
 * SECURE VERSION - Brute Force / Login SQL Injection
 * Uses prepared statements so username/password cannot alter SQL logic.
 *
 * Vulnerable: vulnerabilities/brute/source/low.php
 */

if( isset( $_GET[ 'Login' ] ) ) {
	$user = $_GET[ 'username' ];
	$pass = md5( $_GET[ 'password' ] );

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			$query = "SELECT * FROM `users` WHERE user = ? AND password = ? LIMIT 1";
			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);

			if ($stmt === false) {
				$html .= '<pre>Database error.</pre>';
				break;
			}

			mysqli_stmt_bind_param($stmt, "ss", $user, $pass);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);

			if( $result && mysqli_num_rows( $result ) == 1 ) {
				$row = mysqli_fetch_assoc( $result );
				$html .= "<p>Welcome to the password protected area " . htmlspecialchars($user) . "</p>";
				$html .= "<img src=\"" . htmlspecialchars($row["avatar"]) . "\" />";
			} else {
				$html .= "<pre><br />Username and/or password incorrect.</pre>";
			}

			mysqli_stmt_close($stmt);
			break;

		case SQLITE:
			global $sqlite_db_connection;
			$stmt = $sqlite_db_connection->prepare(
				'SELECT * FROM users WHERE user = ? AND password = ? LIMIT 1'
			);
			if ($stmt) {
				$stmt->bindValue(1, $user, SQLITE3_TEXT);
				$stmt->bindValue(2, $pass, SQLITE3_TEXT);
				$result = $stmt->execute();
				$row = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;

				if ($row) {
					$html .= "<p>Welcome to the password protected area " . htmlspecialchars($user) . "</p>";
					$html .= "<img src=\"" . htmlspecialchars($row["avatar"]) . "\" />";
				} else {
					$html .= "<pre><br />Username and/or password incorrect.</pre>";
				}
				$stmt->close();
			}
			break;
	}
}

?>
