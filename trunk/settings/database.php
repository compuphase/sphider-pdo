<?php
	define('TABLE_PREFIX', "");		/* typically only needed for MySQL */
	define('DATABASE_NAME', 'sphider');	/* only needed for MySQL */
	$db = new PDO('sqlite:'.dirname(__FILE__).'/../sphider.sqlite');
	// $db = new PDO('mysql:host=hostname;dbname='.DATABASE_NAME,'username','password');
?>
