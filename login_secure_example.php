<?php
/**
 * SECURE VERSION - Login Page
 * Uses prepared statements to prevent SQL injection
 * 
 * File: login_secure.php
 * Demonstrates secure authentication with prepared statements
 */

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( ) );
dvwaDatabaseConnect();

if( isset( $_POST[ 'Login' ] ) ) {
	// Anti-CSRF
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}

	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'login.php' );

	// Get input directly (will be parameterized in query)
	$user = $_POST[ 'username' ];
	$pass = $_POST[ 'password' ];
	
	// Remove slashes (avoid double-escaping)
	$user = stripslashes( $user );
	$pass = stripslashes( $pass );
	
	// Hash password (must be done before parameterization)
	$pass = md5( $pass );

	// === VULNERABLE VERSION (for comparison) ===
	// $query  = "SELECT * FROM `users` WHERE user='$user' AND password='$pass';";
	// $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
	
	// === SECURE VERSION (PREPARED STATEMENT) ===
	$query = "SELECT * FROM `users` WHERE user = ? AND password = ? LIMIT 1";
	
	// Prepare the statement
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
	
	if ($stmt === false) {
		dvwaMessagePush( 'Database error occurred' );
		dvwaRedirect( 'login.php' );
		exit;
	}
	
	// Bind parameters: "ss" = string, string
	if (!mysqli_stmt_bind_param($stmt, "ss", $user, $pass)) {
		dvwaMessagePush( 'Error binding parameters' );
		dvwaRedirect( 'login.php' );
		exit;
	}
	
	// Execute query (user input is safe from SQL injection)
	if (!mysqli_stmt_execute($stmt)) {
		dvwaMessagePush( 'Error executing query' );
		dvwaRedirect( 'login.php' );
		exit;
	}
	
	// Get results
	$result = mysqli_stmt_get_result($stmt);
	
	// Check if authentication successful
	if( $result && mysqli_num_rows( $result ) == 1 ) {    
		// Login Successful...
		$row = mysqli_fetch_assoc($result);
		
		dvwaMessagePush( "You have logged in as '{$user}'" );
		dvwaLogin( $user );
		dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'index.php' );
	} else {
		// Login failed
		dvwaMessagePush( 'Login failed' );
		dvwaRedirect( 'login.php' );
	}
	
	mysqli_stmt_close($stmt);
}

$messagesHtml = messagesPopAllToHtml();

// ... rest of HTML rendering code ...

?>
