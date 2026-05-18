<?php
/**
 * SECURE VERSION - SQL Injection (Blind)
 * Uses prepared statements and integer validation.
 *
 * Vulnerable: vulnerabilities/sqli_blind/source/low.php
 */

if( isset( $_GET[ 'Submit' ] ) ) {
	$id = $_GET[ 'id' ];
	$exists = false;

	if( is_numeric( $id ) ) {
		$id = (int) $id;

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				$query = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1";
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);

				if ($stmt) {
					mysqli_stmt_bind_param($stmt, "i", $id);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);
					$exists = ($result && mysqli_num_rows($result) > 0);
					mysqli_stmt_close($stmt);
				}
				break;

			case SQLITE:
				global $sqlite_db_connection;
				$stmt = $sqlite_db_connection->prepare(
					'SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1'
				);
				if ($stmt) {
					$stmt->bindValue(1, $id, SQLITE3_INTEGER);
					$result = $stmt->execute();
					$exists = ($result && $result->fetchArray() !== false);
					$stmt->close();
				}
				break;
		}
	}

	if ($exists) {
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
