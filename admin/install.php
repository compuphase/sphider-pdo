<html>
	<head>
	<title>
	Sphider installation script.
	</title>
	<LINK REL=STYLESHEET HREF="admin.css" TYPE="TEXT/css">
	</head>
<body>
<h2>Sphider installation script.</h2>
<ul>
<?
error_reporting (E_ALL | E_STRICT);
$settings_dir = "../settings";
include "$settings_dir/database.php";

echo "<li>sites";
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
echo " ok\n";

echo "<li>links";
$db->exec("create table ".$table_prefix."[links] (
	link_id INTEGER  PRIMARY KEY NOT NULL,
	site_id INTEGER,
	url VARCHAR(255) NOT NULL UNIQUE,
	title VARCHAR(200),
	description VARCHAR(255),
	language VARCHAR(2),
	fulltxt TEXT,
	indexdate date,
	size FLOAT(2),
	md5sum VARCHAR(32) UNIQUE,
	visible INTEGER DEFAULT 0,
	level INTEGER)");
echo " ok\n";

echo "<li>keywords";
$db->exec("create table ".$table_prefix."[keywords] (
	keyword_id INTEGER PRIMARY KEY NOT NULL,
	keyword VARCHAR(30) NOT NULL UNIQUE,
	metaphone1 VARCHAR(4),
	metaphone2 VARCHAR(4)
	)");
echo " ok\n";

for ($i=0;$i<=15; $i++) {
	$char = dechex($i);
	echo "<li>link_keyword$char";
	$db->exec("create table ".$table_prefix."link_keyword$char (
		link_id INTEGER KEY NOT NULL,
		keyword_id INTEGER KEY NOT NULL,
		weight INTEGER(3),
		domain INTEGER(4)
		)");
    echo " ok\n";
}

echo "<li>categories";
$db->exec("create table ".$table_prefix."categories (
	category_id INTEGER PRIMARY KEY NOT NULL,
	category TEXT,
	parent_num INTEGER
	)");
echo " ok\n";

echo "<li>site_category";
$db->exec("create table ".$table_prefix."site_category (
	site_id INTEGER,
	category_id INTEGER
	)");
echo " ok\n";

echo "<li>temp";
$db->exec("create table ".$table_prefix."temp (
	link VARCHAR(255),
	level INTEGER,
	id VARCHAR (32)
	)");
echo " ok\n";

echo "<li>pending";
$db->exec("create table ".$table_prefix."pending (
	site_id INTEGER,
	temp_id VARCHAR(32),
	level INTEGER,
	count INTEGER,
	num INTEGER
)");
echo " ok\n";

echo "<li>query_log";
$db->exec("create table ".$table_prefix."query_log (
	query VARCHAR(255),
	time timestamp(14),
	elapsed FLOAT(2),
	results INTEGER
	)");
echo " ok\n";

echo "<li>domains";
$db->exec("create table ".$table_prefix."domains (
	domain_id INTEGER  PRIMARY KEY NOT NULL,
	domain VARCHAR(255))");
echo " ok\n";
?>
</ul>

<?
if (@$error >0) {
	print "<b>Creating tables failed. Consult the above error messages.</b>";
} else {
	print "<b>Creating tables successfully completed. Go to <a href=\"admin.php\">admin.php</a> to start indexing.</b>";
}
?>
</body>
</html>