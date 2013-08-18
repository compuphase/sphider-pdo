<?php

	/* ***** please adjust the user name and the ***** */

	define("ADMIN", "admin");	/* user name and password for the site administrator */
	define("PASSW", "admin");
	define('CONFIGSET', 1); 	/* if set to zero, changing the configuration via the web interface is disabled */

	/* ***** no customizations below this point ***** */

	error_reporting(E_ERROR);
	session_start();

if (isset($_POST['user']) && isset($_POST['pass'])) {

	$username = $_POST['user'];
	$password = $_POST['pass'];
	if (($username == ADMIN) && ($password == PASSW)) {
		$_SESSION['admin'] = $username;
		$_SESSION['admin_pw'] = $password;
	} else {
		sleep(3);
	}
	header("Location: admin.php");
    exit();
} elseif (isset($_SESSION['admin']) && isset($_SESSION['admin_pw']) &&$_SESSION['admin'] == ADMIN && $_SESSION['admin_pw'] == PASSW) {

} else {

	?>
	<html>
	<head>
	<title>Sphider Admin Login</title>
		<LINK REL=STYLESHEET HREF="admin.css" TYPE="text/css">
	</head>

	<body>
	<center>
	<br><br>

	<fieldset style="width:30%;"><legend><b>Sphider Admin Login</b></legend>
	<form action="auth.php" method="post">

	<table>
	<tr><td>Username</td><td><input type="text" name="user"></td></tr>
	<tr><td>Password</td><td><input type="password" name="pass"></td></tr>
	<tr><td></td><td><input type="submit" value="Log in" id="submit"></td>
	</tr></table>
	</form>
	</fieldset>
	</center>
	</body>
	</html>
<?php
	exit();
}

$settings_dir = "../settings";
include_once "$settings_dir/database.php";

?>