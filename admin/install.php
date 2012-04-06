<html>
	<head>
	<title>
	Sphider installation script.
	</title>
	<LINK REL=STYLESHEET HREF="admin.css" TYPE="TEXT/css">
	</head>
<body>
<h2>Sphider installation script.</h2>
<?
error_reporting (E_ALL | E_STRICT);
$settings_dir = "../settings";
include "$settings_dir/database.php";

echo "<p>sites\n";
$error = 0;
$db->exec("create table ".$table_prefix."[sites](
	site_id INTEGER PRIMARY KEY NOT NULL,
	url VARCHAR(255),
	title VARCHAR(255),
	short_desc TEXT,
	indexdate date,
	spider_depth INTEGER DEFAULT 2,
	required TEXT,
	disallowed TEXT,
	can_leave_domain bool)");

echo "<p>links\n";
$db->exec("create table ".$table_prefix."[links] (
	link_id INTEGER  PRIMARY KEY NOT NULL,
	site_id INTEGER,
	url VARCHAR(255) NOT NULL UNIQUE,
	title VARCHAR(200),
	description VARCHAR(255),
	fulltxt TEXT,
	indexdate date,
	size FLOAT(2),
	md5sum VARCHAR(32) UNIQUE,
	visible INTEGER DEFAULT 0,
	level INTEGER)");

echo "<p>keywords\n";
$db->exec("create table ".$table_prefix."[keywords] (
	keyword_id INTEGER PRIMARY KEY NOT NULL,
	keyword VARCHAR(30) NOT NULL UNIQUE,
	metaphone1 VARCHAR(4),
	metaphone2 VARCHAR(4)
	)");


for ($i=0;$i<=15; $i++) {
	$char = dechex($i);
	echo "<p>link_keyword$char\n";
	$db->exec("create table ".$table_prefix."link_keyword$char (
		link_id INTEGER KEY NOT NULL,
		keyword_id INTEGER KEY NOT NULL,
		weight INTEGER(3),
		domain INTEGER(4)
		)");
}

echo "<p>categories\n";
$db->exec("create table ".$table_prefix."categories (
	category_id INTEGER PRIMARY KEY NOT NULL,
	category TEXT,
	parent_num INTEGER
	)");

echo "<p>site_category\n";
$db->exec("create table ".$table_prefix."site_category (
	site_id INTEGER,
	category_id INTEGER
	)");

echo "<p>temp\n";
$db->exec("create table ".$table_prefix."temp (
	link VARCHAR(255),
	level INTEGER,
	id VARCHAR (32)
	)");

echo "<p>pending\n";
$db->exec("create table ".$table_prefix."pending (
	site_id INTEGER,
	temp_id VARCHAR(32),
	level INTEGER,
	count INTEGER,
	num INTEGER
)");

echo "<p>query_log\n";
$db->exec("create table ".$table_prefix."query_log (
	query VARCHAR(255),
	time timestamp(14),
	elapsed FLOAT(2),
	results INTEGER
	)");

echo "<p>domains\n";
$db->exec("create table ".$table_prefix."domains (
	domain_id INTEGER  PRIMARY KEY NOT NULL,
	domain VARCHAR(255))");


if (@$error >0) {
	print "<b>Creating tables failed. Consult the above error messages.</b>";
} else {
	print "<b>Creating tables successfully completed. Go to <a href=\"admin.php\">admin.php</a> to start indexing.</b>";
}
?>
</body>
</html>