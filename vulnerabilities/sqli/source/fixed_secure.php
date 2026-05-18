<?php
/**
 * SECURE VERSION - SQL Injection (Impossible Level)
 * Uses prepared statements to prevent SQL injection
 * 
 * Original vulnerable code location: vulnerabilities/sqli/source/low.php
 * Fixed code location: vulnerabilities/sqli/source/fixed_secure.php
 */

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database using PREPARED STATEMENT
			// Placeholders (?) ensure user input is treated as data, not code
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?";
			
			// Prepare the statement
			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
			
			if ($stmt === false) {
				$html .= '<pre>Prepare failed: ' . htmlspecialchars(mysqli_error($GLOBALS["___mysqli_ston"])) . '</pre>';
				$html .= '<pre>Query: ' . htmlspecialchars($query) . '</pre>';
				break;
			}
			
			// Bind parameter: "i" = integer, $id = value
			if (!mysqli_stmt_bind_param($stmt, "i", $id)) {
				$html .= '<pre>Bind param failed: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '</pre>';
				break;
			}
			
			// Execute the query (input is safely escaped)
			if (!mysqli_stmt_execute($stmt)) {
				$html .= '<pre>Execute failed: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '</pre>';
				break;
			}
			
			// Get results
			$result = mysqli_stmt_get_result($stmt);
			
			if (mysqli_num_rows($result) == 0) {
				$html .= '<pre>No records found.</pre>';
			} else {
				while( $row = mysqli_fetch_assoc( $result ) ) {
					// Get values
					$first = $row["first_name"];
					$last  = $row["last_name"];

					// Feedback for end user
					$html .= "<pre>ID: " . htmlspecialchars($id) . "<br />First name: " . htmlspecialchars($first) . "<br />Surname: " . htmlspecialchars($last) . "</pre>";
				}
			}
			
			mysqli_stmt_close($stmt);
			break;
			
		case SQLITE:
			// Similar approach for SQLite
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
			global $sqlite_db_connection;
			$stmt = $sqlite_db_connection->prepare($query);
			
			if (!$stmt) {
				$html .= '<pre>Prepare failed</pre>';
				break;
			}
			
			$result = $stmt->execute(array($id));
			
			if ($result) {
				while( $row = $result->fetchArray(SQLITE3_ASSOC) ) {
					$first = $row["first_name"];
					$last  = $row["last_name"];
					$html .= "<pre>ID: " . htmlspecialchars($id) . "<br />First name: " . htmlspecialchars($first) . "<br />Surname: " . htmlspecialchars($last) . "</pre>";
				}
			}
			$stmt->close();
			break;
	}
}

?>
