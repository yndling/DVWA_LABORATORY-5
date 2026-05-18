<?php
/**
 * SECURE LOGIN EXAMPLE — SQL injection remediation
 * Uses prepared statements instead of string concatenation.
 * Compare with login.php (vulnerable mysqli_real_escape_string + concatenation).
 */

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( ) );
dvwaDatabaseConnect();

if( isset( $_POST[ 'Login' ] ) ) {
	if (array_key_exists( 'session_token', $_SESSION ) ) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = '';
	}

	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'login_secure_example.php' );

	$user = stripslashes( $_POST[ 'username' ] );
	$pass = md5( stripslashes( $_POST[ 'password' ] ) );

	$query = "SELECT table_schema, table_name, create_time
		FROM information_schema.tables
		WHERE table_schema=? AND table_name='users'
		LIMIT 1";
	$stmt = mysqli_prepare( $GLOBALS['___mysqli_ston'], $query );
	mysqli_stmt_bind_param( $stmt, 's', $_DVWA['db_database'] );
	mysqli_stmt_execute( $stmt );
	$result = mysqli_stmt_get_result( $stmt );
	if( mysqli_num_rows( $result ) != 1 ) {
		dvwaMessagePush( "First time using DVWA.<br />Need to run 'setup.php'." );
		dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'setup.php' );
	}
	mysqli_stmt_close( $stmt );

	$query = 'SELECT * FROM `users` WHERE user = ? AND password = ? LIMIT 1';
	$stmt = mysqli_prepare( $GLOBALS['___mysqli_ston'], $query );

	if( $stmt === false ) {
		dvwaMessagePush( 'Database error occurred' );
		dvwaRedirect( 'login_secure_example.php' );
	}

	mysqli_stmt_bind_param( $stmt, 'ss', $user, $pass );
	mysqli_stmt_execute( $stmt );
	$result = mysqli_stmt_get_result( $stmt );

	if( $result && mysqli_num_rows( $result ) == 1 ) {
		mysqli_stmt_close( $stmt );
		dvwaMessagePush( "You have logged in as '" . htmlspecialchars( $user ) . "'" );
		dvwaLogin( $user );
		dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'index.php' );
	}

	mysqli_stmt_close( $stmt );
	dvwaMessagePush( 'Login failed' );
	dvwaRedirect( 'login_secure_example.php' );
}

$messagesHtml = messagesPopAllToHtml();

Header( 'Cache-Control: no-cache, must-revalidate' );
Header( 'Content-Type: text/html;charset=utf-8' );
Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );

generateSessionToken();

echo "<!DOCTYPE html>
<html lang=\"en-GB\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
		<title>Secure Login Example :: DVWA</title>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/login.css\" />
	</head>
	<body>
	<div id=\"wrapper\">
	<div id=\"header\">
	<br />
	<p><img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/login_logo.png\" /></p>
	<p><strong>Secure login (prepared statements)</strong></p>
	<br />
	</div>
	<div id=\"content\">
	<form action=\"login_secure_example.php\" method=\"post\">
	<fieldset>
			<label for=\"user\">Username</label> <input type=\"text\" class=\"loginInput\" size=\"20\" name=\"username\"><br />
			<label for=\"pass\">Password</label> <input type=\"password\" class=\"loginInput\" AUTOCOMPLETE=\"off\" size=\"20\" name=\"password\"><br />
			<br />
			<p class=\"submit\"><input type=\"submit\" value=\"Login\" name=\"Login\"></p>
	</fieldset>
	" . tokenField() . "
	</form>
	<br />
	{$messagesHtml}
	</div>
	<div id=\"footer\">
	<p><a href=\"login.php\">Standard DVWA login</a> | " . dvwaExternalLinkUrlGet( 'https://github.com/yndling/DVWA_LABORATORY-5', 'DVWA_LABORATORY-5' ) . "</p>
	</div>
	</div>
	</body>
</html>";

?>
