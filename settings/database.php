<?php
	define('TABLE_PREFIX', "");		/* typically only needed for MySQL */
        define('TABLE_FORMAT', 'MyISAM');       /* leave undefined for default table format/engine (only relevant for initial database creation) */
        define('CHARSET', 'utf8');              /* leave undefined for default character set (only relevant for initial database creation) */
	define('DATABASE_NAME', 'sphider');	/* only needed for MySQL */
        define('DATABASE_HOST', 'localhost');   /* only needed for MySQL */
	define('FULLTEXT', 'TEXT');		/* database type for long text strings (default = TEXT) */
	define('AUTOINCREMENT', '');            /* only needed for SQLite (should be removed for MySQL) */
	
	$db = new PDO('sqlite:'.dirname(__FILE__).'/../sphider.sqlite');
	// $db = new PDO('mysql:host='.DATABASE_HOST.'; dbname='.DATABASE_NAME, 'username', 'password');
?>
