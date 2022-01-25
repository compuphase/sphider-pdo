<?php
/*******************************************
* Sphider Version 1.3.*
* This program is licensed under the GNU GPL.
* By Ando Saabas           ando(a t)cs.ioc.ee
********************************************/

error_reporting (E_ALL | E_STRICT);

$include_dir = "../include";
require_once("auth.php");
require_once("$include_dir/commonfuncs.php");
require_once("spiderfuncs.php");
extract($_POST);
$settings_dir = "../settings";
$template_dir = "../templates";
require_once("$settings_dir/conf.php");
set_time_limit(0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Sphider administrator tools</title>
<link rel="stylesheet" href="admin.css" type="text/css" />
</head>
<body>
<?php
if (!isset($f))
    $f= isset($_GET["f"]) ? $_GET["f"] : 2;
$site_funcs = Array (22=> "default",21=> "default",4=> "default", 19=> "default", 1=> "default", 2 => "default", "add_site" => "default", 20=> "default", "edit_site" => "default", 5=>"default");
$stat_funcs = Array ("statistics" => "default",  "delete_log"=> "default");
$settings_funcs = Array ("settings" => "default");
$index_funcs = Array ("index" => "default");
$index_funcs = Array ("index" => "default");
$clean_funcs = Array ("clean" => "default", 15=>"default", 16=>"default", 17=>"default", 23=>"default");
$cat_funcs = Array (11=> "default", 10=> "default", "categories" => "default", "edit_cat"=>"default", "delete_cat"=>"default", "add_cat" => "default", 7=> "default");
$database_funcs = Array ("database" => "default");
?>

<div id="admin">
	<div id="tabs">
		<ul>
		<?php
		if ($stat_funcs[$f] ) {
			$stat_funcs[$f] = "selected";
		} else {
			$stat_funcs[$f] = "default";
		}

		if ($site_funcs[$f] ) {
			$site_funcs[$f] = "selected";
		}else {
			$site_funcs[$f] = "default";
		}

		if ($settings_funcs[$f] ) {
			$settings_funcs[$f] = "selected";
		} else {
			$settings_funcs[$f] = "default";
		}

		if ($index_funcs[$f] ) {
			$index_funcs[$f]  = "selected";
		} else {
			$index_funcs[$f] = "default";
		}

		if ($cat_funcs[$f] ) {
			$cat_funcs[$f]  = "selected";
		} else {
			$cat_funcs[$f] = "default";
		}

		if ($clean_funcs[$f] ) {
			$clean_funcs[$f]  = "selected";
		} else {
			$clean_funcs[$f] = "default";
		}

		if ($database_funcs[$f] ) {
			$database_funcs[$f]  = "selected";
		} else {
			$database_funcs[$f] = "default";
		}
		?>

		<li><a href="admin.php?f=2" id="<?php print $site_funcs[$f]?>">Sites</a>  </li>
		<li><a href="admin.php?f=categories" id="<?php print $cat_funcs[$f]?>">Categories</a></li>
		<li><a href="admin.php?f=index" id="<?php print $index_funcs[$f]?>">Index</a></li>
		<li><a href="admin.php?f=clean" id="<?php print $clean_funcs[$f]?>">Clean tables</a> </li>
        <?php if (CONFIGSET) { ?>
		    <li><a href="admin.php?f=settings" id="<?php print $settings_funcs[$f]?>">Settings</a></li>
        <?php } ?>
		<li><a href="admin.php?f=statistics" id="<?php print $stat_funcs[$f]?>">Statistics</a> </li>
		<?php if ($db->getAttribute(constant("PDO::ATTR_DRIVER_NAME")) == 'mysql') {?>
		    <li><a href="admin.php?f=database" id="<?php print $database_funcs[$f]?>">Database</a></li>
		<?php } ?>
		<li><a href="admin.php?f=24" id="default">Log out</a></li>
		</ul>
	</div>
	<div id="main">

<?php
	function list_cats($parent, $lev, $color, $message) {
		global $db;
		if ($lev == 0) {
			?>
			<div id="submenu">
				<ul>
				<li><a href="admin.php?f=add_cat">Add category</a> </li>
				</ul>
			</div>
			<?php
			print $message;
			print "<br/>";
			print "<br/><div align=\"center\"><center><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\" width =\"600\"><tr><td><table table cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\n";
		}
		$space = "";
		for ($x = 0; $x < $lev; $x++)
			$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";

		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE parent_num=$parent ORDER BY category";
		$result = $db->query($query);
		echo sql_errorstring(__FILE__,__LINE__);

		while ($row = $result->fetch()) {
			if ($color =="white")
				$color = "grey";
			else
				$color = "white";

			$id = $row['category_id'];
			$cat = $row['category'];
			print "<tr class=\"$color\"><td width=90% align=left>$space<a href=\"admin.php?f=edit_cat&cat_id=$id\">".stripslashes($cat). "</a></td><td><a href=\"admin.php?f=edit_cat&cat_id=$id\" id=\"small_button\">Edit</a></td><td> <a href=\"admin.php?f=11&cat_id=$id\" onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" id=\"small_button\">Delete</a></td></tr>\n";

			$color = list_cats($id, $lev + 1, $color, "");
		}

		if ($lev == 0)
			print "</table></td></tr></table></center></div>\n";
		return $color;
	}

	function walk_through_cats($parent, $lev, $site_id) {
		global $db;
		$space = "";
		for ($x = 0; $x < $lev; $x++)
			$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";

		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE parent_num=$parent ORDER BY category";
		$result = $db->query($query);
		echo sql_errorstring(__FILE__,__LINE__);

		while ($row = $result->fetch()) {
			$id = $row['category_id'];
			$cat = $row['category'];
			$state = '';
			if ($site_id <> '') {
				$result2 = $db->query("select * from ".TABLE_PREFIX."site_category where site_id=$site_id and category_id=$id");
				echo sql_errorstring(__FILE__,__LINE__);

				if ($result2->fetch()) {
					$state = "checked";
					$result2->closeCursor();
				}
			}

			print $space . "<input type=checkbox name=cat[$id] $state>" . $cat . "<br/>\n";
			;
			walk_through_cats($id, $lev + 1, $site_id);
		}
	}



function addcatform($parent) {
	global $db;
	$par2 = "";
	$par2num = "";
	?>
	<div id="submenu">
	</div>
	<?php
	 if ($parent=='')
	   $par='(Top level)';
	 else {
		$query = "SELECT category, parent_num FROM ".TABLE_PREFIX."categories WHERE category_id='$parent'";
		$result = $db->query($query);
		if (!sql_errorstring(__FILE__,__LINE__)) {
			if ($row = $result->fetch()) {
				$par=$row[0];
				$query = "SELECT Category_ID, Category FROM ".TABLE_PREFIX."categories WHERE Category_ID='$row[1]'";
				$result2 = $db->query($query);
				echo sql_errorstring(__FILE__,__LINE__);
				if ($row = $result2->fetch()) {
					$par2num = $row[0];
					$par2 = $row[1];
				} else {
					$par2 = "Top level";
				}
				$result2->closeCursor();
			}
		} else {
			echo sql_errorstring(__FILE__,__LINE__);
		}
		$result->closeCursor();
		print "</td></tr></table>";
	}

?>
	   <br/><center><table><tr><td valign=top align=center colspan=2><b>Parent: <?php print "<a href=admin.php?f=add_cat&parent=$par2num>$par2</a> >".stripslashes($par)?></b></td></tr>
		<form action=admin.php method=post>
		<input type=hidden name=f value=7>
		<input type=hidden name=parent value="<?php print $parent?>"
		<tr><td><b>Category:</b></td><td> <input type=text name=category size=40></td></tr>
		<tr><td></td><td><input type=submit id="submit" value=Add></td></tr></form>

<?php
	print "<tr><td colspan=2>";
	$query = "SELECT category_ID, Category FROM ".TABLE_PREFIX."categories WHERE parent_num='$parent'";
	$result = $db->query($query);
	echo sql_errorstring(__FILE__,__LINE__);
	if ($row = $result->fetch()) {
		print "<br/><b>Create subcategory under</b><br/><br/>";
		print "<a href=\"admin.php?f=add_cat&parent=$row[0]\">".stripslashes($row[1])."</a><br/>";
	}
	while ($row = $result->fetch()) {
		print "<a href=\"admin.php?f=add_cat&parent=$row[0]\">".stripslashes($row[1])."</a><br/>";
	}
	print "</td></tr></table></center>";
}


	function addcat($category, $parent) {
		global $db;
		if (strlen($category) == 0)
			return;
		if ($parent == "")
			$parent = 0;
		$stat = $db->prepare("INSERT INTO ".TABLE_PREFIX."categories (category, parent_num) VALUES (:$category, :parent)");
		$stat->execute(array(':category' => $category, ':parent' => $parent));
		If (!sql_errorstring(__FILE__,__LINE__)) {
			return "<center><b>Category $category added.</b></center>" ;
		} else {
			return sql_errorstring(__FILE__,__LINE__);;
		}
	}



	function addsiteform() {
		?>
		<div id="submenu"><center><b>Add a site</b></center></div>
		<br/><div align=center><center><table>
		<form action=admin.php method=post>
		<input type=hidden name=f value=1>
		<input type=hidden name=af value=2>
		<tr><td><b>URL:</b></td><td align ="right"></td><td><input type=text name=url size=60 value ="http://"></td></tr>
		<tr><td><b>Title:</b></td><td></td><td> <input type=text name=title size=60></td></tr>
		<tr><td><b>Short description:</b></td><td></td><td><textarea name=short_desc cols=45 rows=3 wrap="virtual"></textarea></td></tr>
		<tr><td>Category:</td><td></td><td>
		<?php  walk_through_cats(0, 0, '');?></td></tr>
		<tr><td></td><td></td><td><input type=submit id="submit" value=Add></td></tr></form></table></center></div>
		<?php
	}

	function editsiteform($site_id) {
		global $db;
		$result = $db->query("SELECT site_id, url, title, short_desc, spider_depth, required, disallowed, can_leave_domain from ".TABLE_PREFIX."sites where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		$result->closeCursor();
		$depth = $row['spider_depth'];
		$fullchecked = "";
		$depthchecked = "";
		if ($depth == -1 ) {
			$fullchecked = "checked";
			$depth ="";
		} else {
			$depthchecked = "checked";
		}
		$leave_domain = $row['can_leave_domain'];
		if ($leave_domain == 1 ) {
			$domainchecked = "checked";
		} else {
			$domainchecked = "";
		}
		?>
			<div id="submenu"><center><b>Edit site</b></center></div>
			<br/><div align=center><center><table>
			<form action=admin.php method=post>
			<input type=hidden name=f value=4>
			<input type=hidden name=site_id value=<?php print $site_id;?>>
			<tr><td><b>URL:</b></td><td align ="right"></td><td><input type=text name=url value=<?php print "\"".$row['url']."\""?> size=60></td></tr>
			<tr><td><b>Title:</b></td><td></td><td> <input type=text name=title value=<?php print  "\"".stripslashes($row['title'])."\""?> size=60></td></tr>
			<tr><td><b>Short description:</b></td><td></td><td><textarea name=short_desc cols=45 rows=3 wrap><?php print stripslashes($row['short_desc'])?></textarea></td></tr>
			<tr><td><b>Spidering options:</b></td><td></td><td><input type="radio" name="soption" value="full" <?php print $fullchecked;?>> Full<br/>
			<input type="radio" name="soption" value="level" <?php print $depthchecked;?>>To depth: <input type="text" name="depth" size="2" value="<?php print $depth;?>"><br/>
			<input type="checkbox" name="domaincb" value="1" <?php print $domainchecked;?>> Spider can leave domain
			</td></tr>
			<tr><td><b>URLs must include:</b></td><td></td><td><textarea name=in cols=45 rows=2 wrap="virtual"><?php print $row['required'];?></textarea></td></tr>
			<tr><td><b>URLs must not include:</b></td><td></td><td><textarea name=out cols=45 rows=2 wrap="virtual"><?php print $row['disallowed'];?></textarea></td></tr>

			<tr><td>Category:</td><td></td><td>
			<?php  walk_through_cats(0, 0, $site_id);?></td></tr>
			<tr><td></td><td></td><td><input type="submit"  id="submit"  value="Update"></td></tr></form></table></center></div>
		<?php
		}


		function editsite($site_id, $url, $title, $short_desc, $depth, $required, $disallowed, $domaincb,  $cat) {
			global $db;
			// get the current "root path"
			$result = $db->query("select url from ".TABLE_PREFIX."sites where site_id=$site_id");
			if ($result) {
			  $row = $result->fetch();
			  $old_url = remove_file_from_url($row[0]);
			} else {
			  $old_url = "";
			}
			$result->closeCursor();
			//??? split the domain, set it
			$short_desc = $db->quote($short_desc);
			$title = $db->quote($title);
			$db->exec("DELETE FROM ".TABLE_PREFIX."site_category where site_id=$site_id");
			echo sql_errorstring(__FILE__,__LINE__);
			$compurl=parse_url($url);
			if ($compurl['path']=='')
				$url=$url."/";
			$db->exec("UPDATE ".TABLE_PREFIX."sites SET url='$url', title=$title, short_desc=$short_desc, spider_depth =$depth, required='$required', disallowed='$disallowed', can_leave_domain=$domaincb WHERE site_id=$site_id");
			echo sql_errorstring(__FILE__,__LINE__);
			$result=$db->query("select category_id from ".TABLE_PREFIX."categories");
			echo sql_errorstring(__FILE__,__LINE__);
			print sql_errorstring(__FILE__,__LINE__);
			while ($row=$result->fetch()) {
				$cat_id=$row[0];
				if ($cat[$cat_id]=='on') {
					$db->exec("INSERT INTO ".TABLE_PREFIX."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
					echo sql_errorstring(__FILE__,__LINE__);
				}
			}
			/* update all links */
			$new_url = remove_file_from_url($url);
			if (strcasecmp($new_url, $old_url) != 0) {
			  $result = $db->query("SELECT link_id, url FROM ".TABLE_PREFIX."links WHERE site_id=$site_id");
			  while ($row = $result->fetch()) {
				$link_id = $row[0];
				$link = $row[1];
				$link = substr($link, strlen($old_url));
				$link = $new_url . $link;
				$db->exec("UPDATE ".TABLE_PREFIX."links SET url='$link' WHERE link_id=$link_id");
			  }
			}
			if (!sql_errorstring(__FILE__,__LINE__)) {
				return "<br/><center><b>Site updated.</b></center>" ;
			} else {
				return sql_errorstring(__FILE__,__LINE__);;
			}
		}

	function editcatform($cat_id) {
		global $db;
		$result = $db->query("SELECT category FROM ".TABLE_PREFIX."categories where category_id='$cat_id'");
		echo sql_errorstring(__FILE__,__LINE__);
		$row=$result->fetch();
		$result->closeCursor();
		$category=$row[0];
		?>
			<div id="submenu"><center><b>Edit category</b></center></div>
			<br/>
		   <div align="center"><center><table>
			<form action="admin.php" method="post">
			<input type="hidden" name="f" value="10">
			<input type="hidden" name="cat_id" value="<?php  print $cat_id;?>"
			<tr><td><b>Category:</b></td><td> <input type="text" name="category" value="<?php print $category?>"size=40></td></tr>
			<tr><td></td><td><input type="submit"  id="submit"  value="Update"></td></tr></form></table></center></div>
		<?php
		}


	function editcat($cat_id, $category) {
		global $db;
		$qry = "UPDATE ".TABLE_PREFIX."categories SET category=".$db->quote($category)." WHERE category_id='$cat_id'";
		$db->exec($qry);
		if (!sql_errorstring(__FILE__,__LINE__))	{
			return "<br/><center><b>Category updated</b></center>";
		} else {
			return sql_errorstring(__FILE__,__LINE__);
		}
	}



	function showsites($message = "") {
		global $db;
		$result = $db->query("SELECT site_id, url, title, indexdate from ".TABLE_PREFIX."sites ORDER By indexdate, title");
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		?>
		<div id='submenu'>
		 <ul>
		  <li><a href='admin.php?f=add_site'>Add site</a> </li>
		  <?php
			if ($row) {
				?>
				<li><a href='spider.php?all=1'> Reindex all</a></li>
				<?php
			}
			?>
		 </ul>
		</div>

		<?php
		print $message;
		print "<br/>";
		if ($row) {
			print "<div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing=\"1\">
			<tr class=\"grey\"><td align=\"center\"><b>Site name</b></td><td align=\"center\"><b>Site url</b></td><td align=\"center\"><b>Last indexed</b></td><td colspan=4></td></tr>\n";
		} else {
			?><center><p><b>Welcome to Sphider. <br><br>Choose "Add site" from the submenu to add a new site, or "Index" to directly go to the indexing section.</b></p></center><?php
		}
		$class = "grey";
		$numrows = 0;
		while ($row) {
			if ($row['indexdate']=='') {
				$indexstatus="<font color=\"red\">Not indexed</font>";
				$indexoption="<a href=\"admin.php?f=index&url=$row[url]\">Index</a>";
			} else {
				$site_id = $row['site_id'];
				$result2 = $db->query("SELECT site_id from ".TABLE_PREFIX."pending where site_id =$site_id");
				echo sql_errorstring(__FILE__,__LINE__);
				$row2=$result2->fetch();
				if ($row2['site_id'] == $row['site_id']) {
					$indexstatus = "Unfinished";
					$indexoption="<a href=\"admin.php?f=index&url=$row[url]\">Continue</a>";
				} else {
					$indexstatus = $row['indexdate'];
					$indexoption="<a href=\"admin.php?f=index&url=$row[url]&reindex=1\">Re-index</a>";
				}
				$result2->closeCursor();
			}
			if ($class =="white")
				$class = "grey";
			else
				$class = "white";
			print "<tr class=\"$class\"><td align=\"left\">".stripslashes($row['title'])."</td><td align=\"left\"><a href=\"$row[url]\">$row[url]</a></td><td>$indexstatus</td>";
			print "<td><a href=admin.php?f=20&site_id=$row[site_id] id=\"small_button\">Options</a></td></tr>\n";
			$numrows++;
			$row=$result->fetch();
		}
		if ($numrows > 0) {
			print "</table></td></tr></table></div>";
		}
	}

	function deletecat($cat_id) {
		global $db;
		$list = implode(",", get_cats($cat_id));
		$db->exec("delete from ".TABLE_PREFIX."categories where category_id in ($list)");
		echo sql_errorstring(__FILE__,__LINE__);
		$db->exec("delete from ".TABLE_PREFIX."site_category where category_id=$cat_id");
		echo sql_errorstring(__FILE__,__LINE__);
		return "<center><b>Category deleted.</b></center>";
	}
	function deletesite($site_id) {
		global $db;
		$db->exec("delete from ".TABLE_PREFIX."sites where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		$db->exec("delete from ".TABLE_PREFIX."site_category where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		$query = "select link_id from ".TABLE_PREFIX."links where site_id=$site_id";
		$result = $db->query($query);
		echo sql_errorstring(__FILE__,__LINE__);
		$todelete = array();
		while ($row=$result->fetch()) {
			$todelete[]=$row['link_id'];
		}

		if (count($todelete)>0) {
			$todelete = implode(",", $todelete);
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$query = "delete from ".TABLE_PREFIX."link_keyword$char where link_id in($todelete)";
				$db->exec($query);
				echo sql_errorstring(__FILE__,__LINE__);
			}
		}

		$db->exec("delete from ".TABLE_PREFIX."links where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		$db->exec("delete from ".TABLE_PREFIX."pending where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		return "<br/><center><b>Site deleted</b></center>";
	}

	function deletePage($link_id) {
		global $db;
		$db->exec("delete from ".TABLE_PREFIX."links where link_id=$link_id");
		echo sql_errorstring(__FILE__,__LINE__);
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			$db->exec("delete from ".TABLE_PREFIX."link_keyword$char where link_id=$link_id");
		}
		echo sql_errorstring(__FILE__,__LINE__);
		return "<br/><center><b>Page deleted</b></center>";
	}


	function cleanTemp() {
		global $db;
		$del = $db->exec("delete from ".TABLE_PREFIX."temp where level >= 0");
		echo sql_errorstring(__FILE__,__LINE__);
				?>
		<div id="submenu">
		</div><?php
		print "<br/><center><b>Temp table cleared, $del items deleted.</b></center>";
	}

	function clearLog() {
		global $db;
		$del = $db->exec("delete from ".TABLE_PREFIX."query_log where time >= 0");
		echo sql_errorstring(__FILE__,__LINE__);
		?>
	<div id="submenu">
	</div><?php
		print "<br/><center><b>Search log cleared, $del items deleted.</b></center>";
	}


	function cleanLinks() {
		global $db;
		$query = "select site_id from ".TABLE_PREFIX."sites";
		$result = $db->query($query);
		echo sql_errorstring(__FILE__,__LINE__);
		$todelete = array();
		if ($row = $result->fetch()) {
			$todelete[]=$row['site_id'];
			while ($row=$result->fetch()) {
				$todelete[]=$row['site_id'];
			}
			$todelete = implode(",", $todelete);
			$sql_end = " not in ($todelete)";
		}

		$result = $db->query("select link_id from ".TABLE_PREFIX."links where site_id".$sql_end);
		echo sql_errorstring(__FILE__,__LINE__);
		$del = 0;
		//??? get all results in an array, so that the cursor is closed
		while ($row=$result->fetch()) {
			$link_id=$row[link_id];
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$db->exec("delete from ".TABLE_PREFIX."link_keyword$char where link_id=$link_id");
				echo sql_errorstring(__FILE__,__LINE__);
			}
			$db->exec("delete from ".TABLE_PREFIX."links where link_id=$link_id");
			echo sql_errorstring(__FILE__,__LINE__);
			$del++;
		}

		$result = $db->query("select link_id from ".TABLE_PREFIX."links where site_id is NULL");
		echo sql_errorstring(__FILE__,__LINE__);
		//??? get all results in an array, so that the cursor is closed
		while ($row=$result->fetch()) {
			$link_id=$row[link_id];
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$db->exec("delete from ".TABLE_PREFIX."link_keyword$char where link_id=$link_id");
				echo sql_errorstring(__FILE__,__LINE__);
			}
			$db->exec("delete from ".TABLE_PREFIX."links where link_id=$link_id");
			echo sql_errorstring(__FILE__,__LINE__);
			$del++;
		}
		?>
		<div id="submenu">
		</div><?php
		print "<br/><center><b>Links table cleaned, $del links deleted.</b></center>";
	}

	function cleanKeywords() {
		global $db;
		$query = "select keyword_id, keyword from ".TABLE_PREFIX."keywords";
		$result = $db->query($query);
		echo sql_errorstring(__FILE__,__LINE__);
		$del = 0;
		/* get all results in an array, so that the cursor is closed, then run
		   over the array */
		$keywordlist = array();
		while ($row=$result->fetch())
			$keywordlist[$row['keyword_id']] = $row['keyword'];
		foreach ($keywordlist as $keyId => $keyword) {
			$wordmd5 = substr(md5($keyword), 0, 1);
			$query = "SELECT keyword_id FROM ".TABLE_PREFIX."link_keyword$wordmd5 WHERE keyword_id=$keyId";
			$result2 = $db->query($query);
			echo sql_errorstring(__FILE__,__LINE__);
			if (! $result2->fetch()) {
				$db->exec("DELETE FROM ".TABLE_PREFIX."keywords WHERE keyword_id=$keyId");
				echo sql_errorstring(__FILE__,__LINE__);
				$del++;
			}
		}
		unset($keywordlist);
		?>
	<div id="submenu">
	</div><?php
		print "<br/><center><b>Keywords table cleaned, $del keywords deleted.</b></center>";
	}

	function getStatistics() {
		global $db;
		$stats = array();
		$keywordQuery = "select count(keyword_id) from ".TABLE_PREFIX."keywords";
		$linksQuery = "select count(url) from ".TABLE_PREFIX."links";
		$siteQuery = "select count(site_id) from ".TABLE_PREFIX."sites";
		$categoriesQuery = "select count(category_id) from ".TABLE_PREFIX."categories";

		$result = $db->query($keywordQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$stats['keywords']=$row[0];
		}
		$result->closeCursor();
		$result = $db->query($linksQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$stats['links']=$row[0];
		}
		$result->closeCursor();
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			$result = $db->query("select count(link_id) from ".TABLE_PREFIX."link_keyword$char");
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row=$result->fetch()) {
				$stats['index']+=$row[0];
			}
			$result->closeCursor();
		}
		$result = $db->query($siteQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$stats['sites']=$row[0];
		}
		$result->closeCursor();
		$result = $db->query($categoriesQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$stats['categories']=$row[0];
		}
		$result->closeCursor();
		return $stats;
	}

	function addsite($url, $title, $short_desc, $cat) {
		global $db;
		$short_desc = $db->quote($short_desc);
		$title = $db->quote($title);
		$compurl=parse_url("".$url);
		if ($compurl['path']=='')
			$url=$url."/";
		$result = $db->query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
		echo sql_errorstring(__FILE__,__LINE__);
		if (! $result->fetch()) {
			$db->query("INSERT INTO ".TABLE_PREFIX."sites (url, title, short_desc) VALUES ('$url', $title, $short_desc)");
			echo sql_errorstring(__FILE__,__LINE__);
			$result = $db->query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
			echo sql_errorstring(__FILE__,__LINE__);
			$row = $result->fetch();
			$site_id = $row[0];
			$result=$db->query("select category_id from ".TABLE_PREFIX."categories");
			echo sql_errorstring(__FILE__,__LINE__);
			while ($row=$result->fetch()) {
				$cat_id=$row[0];
				if ($cat[$cat_id]=='on') {
					$db->query("INSERT INTO ".TABLE_PREFIX."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
					echo sql_errorstring(__FILE__,__LINE__);
				}
			}

			If (!sql_errorstring(__FILE__,__LINE__))	{
				$message =  "<br/><center><b>Site added</b></center>" ;
			} else {
				$message = sql_errorstring(__FILE__,__LINE__);;
			}

		} else {
			$message = "<center><b>Site already in database</b></center>";
		}
		return $message;
	}

	function indexscreen($url, $reindex) {
		global $db;
		$check = "";
		$levelchecked = "checked";
		$spider_depth = 2;
		if ($url=="") {
			$url = "http://";
			$advurl = "";
		} else {
			$advurl = $url;
			$result = $db->query("select spider_depth, required, disallowed, can_leave_domain from ".TABLE_PREFIX."sites " .
					"where url='$url'");
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row = $result->fetch()) {
				$spider_depth = $row[0];
				if ($spider_depth == -1 ) {
					$fullchecked = "checked";
					$spider_depth ="";
					$levelchecked = "";
				}
				$must = $row[1];
				$mustnot = $row[2];
				$canleave = $row[3];
			}
			$result->closeCursor();
		}

		?>
		<div id="submenu">
			<ul>
				<li>
				<?php
				if ($must !="" || $mustnot !="" || $canleave == 1 ) {
					$_SESSION['index_advanced']=1;
				}
				if ($_SESSION['index_advanced']==1){
					print "<a href='admin.php?f=index&adv=0&url=$advurl'>Hide advanced options</a>";
				} else {
					print "<a href='admin.php?f=index&adv=1&url=$advurl'>Advanced options</a>";
				}

				?>
				</li>
			</ul>
		</div>
		<br/>
		<div id="indexoptions"><table>
		<form action="spider.php" method="post">
		<tr><td><b>Address:</b></td><td> <input type="text" name="url" size="48" value=<?php print "\"$url\"";?>></td></tr>
		<tr><td><b>Indexing options:</b></td><td>
		<input type="radio" name="soption" value="full" <?php print $fullchecked;?>> Full<br/>
		<input type="radio" name="soption" value="level" <?php print $levelchecked;?>>To depth: <input type="text" name="maxlevel" size="2" value="<?php print $spider_depth;?>"><br/>
		<?php if ($reindex==1) $check="checked"?>
		<input type="checkbox" name="reindex" value="1" <?php print $check;?>> Reindex<br/>
		</td></tr>
		<?php
		if ($_SESSION['index_advanced']==1){
			?>
			<?php if ($canleave==1) {$checkcan="checked" ;} ?>
			<tr><td></td><td><input type="checkbox" name="domaincb" value="1" <?php print $checkcan;?>> Spider can leave domain <!--a href="javascript:;" onClick="window.open('hmm','newWindow','width=300,height=300,left=600,top=200,resizable');" >?</a--><br/></td></tr>
			<tr><td><b>URL must include:</b></td><td><textarea name=in cols=35 rows=2 wrap="virtual"><?php print $must;?></textarea></td></tr>
			<tr><td><b>URL must not include:</b></td><td><textarea name=out cols=35 rows=2 wrap="virtual"><?php print $mustnot;?></textarea></td></tr>
			<?php
		}
		?>

		<tr><td></td><td><input type="submit" id="submit" value="Start indexing"></td></tr>
		</form></table></div>
		<?php
	}

	function siteScreen($site_id, $message = "") {
		global $db;
		$result = $db->query("SELECT site_id, url, title, short_desc, indexdate from ".TABLE_PREFIX."sites where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		$row=$result->fetch();
		$url = replace_ampersand($row['url']);
		if ($row['indexdate']=='') {
			$indexstatus="<font color=\"red\">Not indexed</font>";
			$indexoption="<a href=\"admin.php?f=index&url=$url\">Index</a>";
		} else {
			$site_id = $row['site_id'];
			$result2 = $db->query("SELECT site_id from ".TABLE_PREFIX."pending where site_id =$site_id");
			echo sql_errorstring(__FILE__,__LINE__);

			$row2 = $result->fetch();
			if ($row2['site_id'] == $row['site_id']) {
				$indexstatus = "Unfinished";
				$indexoption="<a href=\"admin.php?f=index&url=$url\">Continue indexing</a>";

			} else {
				$indexstatus = $row['indexdate'];
				$indexoption="<a href=\"admin.php?f=index&url=$url&reindex=1\">Re-index</a>";
			}
			$result2->closeCursor();
		}
		?>

		<div id="submenu">
		</div>
		<?php print $message;?>
			<br/>

		<center>
		<div style="width:755px;">
		<div style="float:left; margin-right:0px;">
		<div class="darkgrey">
		<table cellpadding="3" cellspacing="0">

			<table  cellpadding="5" cellspacing="1" width="640">
			  <tr >
				<td class="grey" valign="top" width="20%" align="left">URL:</td>
				<td class="white" align="left"><a href="<?php print  $row['url']; print "\">"; print $row['url'];?></a></td>
			  </tr>
			<tr>
				<td class="grey" valign="top" align="left">Title:</td>
				<td class="white" align="left"><b><?php print stripslashes($row['title']);?></b></td>
			</tr>
			  <tr>
				<td class="grey" valign="top" align="left">Description:</td>
				<td width="80%" class="white"  align="left"><?php print stripslashes($row['short_desc']);?></td>
			  </tr>
			  <tr>
				<td class="grey" valign="top" align="left">Last indexed:</td>
				<td class="white"  align="left"><?php print $indexstatus;?></td>
			  </tr>
			</table>
		</div>
		</div>
		<div id= "vertmenu">
		<ul>
		<?php if (CONFIGSET) { ?>
		    <li><a href=admin.php?f=edit_site&site_id=<?php print  $row['site_id']?>>Edit</a></li>
		<?php } ?>
		<li><?php print $indexoption?></li>
		<?php if (CONFIGSET) { ?>
		    <li><a href=admin.php?f=21&site_id=<?php print  $row['site_id']?>>Browse pages</a></li>
		    <li><a href=admin.php?f=5&site_id=<?php print  $row['site_id'];?> onclick="return confirm('Are you sure you want to delete? Index will be lost.')">Delete</a></li>
		<?php } ?>
		<li><a href=admin.php?f=19&site_id=<?php print  $row['site_id'];?>>Stats</a></li>
		</div>
		</ul>
		</div>
		</center>
		<div class="clear">
		</div>
		<br/>
	<?php
	}

	function siteStats($site_id) {
		global $db;
		$result = $db->query("select url from ".TABLE_PREFIX."sites where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$url=$row[0];

			$lastIndexQuery = "SELECT indexdate from ".TABLE_PREFIX."sites where site_id = $site_id";
			$sumSizeQuery = "select sum(length(fulltxt)) from ".TABLE_PREFIX."links where site_id = $site_id";
			$siteSizeQuery = "select sum(size) from ".TABLE_PREFIX."links where site_id = $site_id";
			$linksQuery = "select count(*) from ".TABLE_PREFIX."links where site_id = $site_id";

			$result = $db->query($lastIndexQuery);
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row=$result->fetch()) {
				$stats['lastIndex']=$row[0];
			}

			$result = $db->query($sumSizeQuery);
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row=$result->fetch()) {
				$stats['sumSize']=$row[0];
			}
			$result = $db->query($linksQuery);
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row=$result->fetch()) {
				$stats['links']=$row[0];
			}

			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$result = $db->query("select count(*) from ".TABLE_PREFIX."links, ".TABLE_PREFIX."link_keyword$char where ".TABLE_PREFIX."links.link_id=".TABLE_PREFIX."link_keyword$char.link_id and ".TABLE_PREFIX."links.site_id = $site_id");
				echo sql_errorstring(__FILE__,__LINE__);
				if ($row=$result->fetch()) {
					$stats['index']+=$row[0];
				}
			}
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$wordQuery = "select count(distinct keyword) from ".TABLE_PREFIX."keywords, ".TABLE_PREFIX."links, ".TABLE_PREFIX."link_keyword$char where ".TABLE_PREFIX."links.link_id=".TABLE_PREFIX."link_keyword$char.link_id and ".TABLE_PREFIX."links.site_id = $site_id and ".TABLE_PREFIX."keywords.keyword_id = ".TABLE_PREFIX."link_keyword$char.keyword_id";
				$result = $db->query($wordQuery);
				echo sql_errorstring(__FILE__,__LINE__);
				if ($row=$result->fetch()) {
					$stats['words']+=$row[0];
				}
			}

			$result = $db->query($siteSizeQuery);
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row=$result->fetch()) {
				$stats['siteSize']=$row[0];
			}
			if ($stats['siteSize']=="")
				$stats['siteSize'] = 0;
			$stats['siteSize'] = number_format($stats['siteSize'], 2);
			print"<div id=\"submenu\"></div>";
			print "<br/><div align=\"center\"><center><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td colspan=\"2\">";
			print "Statistics for site <a href=\"admin.php?f=20&site_id=$site_id\">$url</a>";
			print "<tr class=\"white\"><td>Last indexed:</td><td align=\"center\"> ".$stats['lastIndex']."</td></tr>";
			print "<tr class=\"grey\"><td>Pages indexed:</td><td align=\"center\"> ".$stats['links']."</td></tr>";
			print "<tr class=\"white\"><td>Total index size:</td><td align=\"center\"> ".$stats['index']."</td></tr>";
			$sum = number_format($stats['sumSize']/1024, 2);
			print "<tr class=\"grey\"><td>Cached texts:</td><td align=\"center\"> ".$sum."kb</td></tr>";
			print "<tr class=\"white\"><td>Total number of keywords:</td><td align=\"center\"> ".$stats['words']."</td></tr>";
			print "<tr class=\"grey\"><td>Site size:</td><td align=\"center\"> ".$stats['siteSize']."kb</td></tr>";
			print "</table></td></tr></table></center></div>";
		}
	}

	function browsePages($site_id, $start, $filter, $per_page) {
		global $db;
		$result = $db->query("select url from ".TABLE_PREFIX."sites where site_id=$site_id");
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		$url = $row[0];

		$query_add = "";
		if ($filter != "") {
			$query_add = "and url like '%$filter%'";
		}
		$linksQuery = "select count(*) from ".TABLE_PREFIX."links where site_id = $site_id $query_add";
		$result = $db->query($linksQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		$numOfPages = $row[0];

		$result = $db->query($linksQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		$from = ($start-1) * 10;
		$to = min(($start)*10, $numOfPages);


		$linksQuery = "select link_id, url from ".TABLE_PREFIX."links where site_id = $site_id and url like '%$filter%' order by url limit $from, $per_page";
		$result = $db->query($linksQuery);
		echo sql_errorstring(__FILE__,__LINE__);
		?>
		<div id="submenu"></div>
		<br/>
		<center>
		<b>Pages of site <a href="admin.php?f=20&site_id=<?php  print $site_id?>"><?php print $url;?></a></b><br/>
		<p>
		<form action="admin.php" method="post">
		Urls per page: <input type="text" name="per_page" size="3" value="<?php print $per_page;?>">
		Url contains: <input type="text" name="filter" size="15" value="<?php print $filter;?>">
		<input type="submit" id="submit" value="Filter">
		<input type="hidden" name="start" value="1">
		<input type="hidden" name="site_id" value="<?php print $site_id?>">
		<input type="hidden" name="f" value="21">
		</form>
		</p>
	<table width="600"><tr><td>
		<table cellspacing ="0" cellpadding="0" class="darkgrey" width ="100%"><tr><td>
		<table  cellpadding="3" cellspacing="1" width="100%">

		<?php
		$class = "white";
		while ($row = $result->fetch()) {
			if ($class =="white")
				$class = "grey";
			else
				$class = "white";
			print "<tr class=\"$class\"><td><a href=\"".$row['url']."\">".$row['url']."</a></td><td width=\"8%\"> <a href=\"admin.php?link_id=".$row['link_id']."&f=22&site_id=$site_id&start=1&filter=$filter&per_page=$per_page\">Delete</a></td></tr>";
		}

		print "</table></td></tr></table>";

		$pages = ceil($numOfPages / $per_page);
		$prev = $start - 1;
		$next = $start + 1;

		if ($pages > 0)
			print "<center>Pages: ";

		$links_to_next =10;
		$firstpage = $start - $links_to_next;
		if ($firstpage < 1) $firstpage = 1;
		$lastpage = $start + $links_to_next;
		if ($lastpage > $pages) $lastpage = $pages;

		for ($x=$firstpage; $x<=$lastpage; $x++)
			if ($x<>$start) {
				print "<a href=admin.php?f=21&site_id=$site_id&start=$x&filter=$filter&per_page=$per_page>$x</a> ";
			} else
				print "<b>$x </b>";
		print"</td></tr></table></center>";

	}

	function cleanForm() {
		global $db;
		$result = $db->query("select count(*) from ".TABLE_PREFIX."query_log");
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$log=$row[0];
		}
		$result = $db->query("select count(*) from ".TABLE_PREFIX."temp");
		echo sql_errorstring(__FILE__,__LINE__);
		if ($row=$result->fetch()) {
			$temp=$row[0];
		}

		?>
		<div id="submenu">
		</div>
		<br/><div align="center">
		<table cellspacing ="0" cellpadding="0" class="darkgrey"><tr><td align="left"><table cellpadding="3" cellspacing = "1"  width="100%"><tr class="grey"  ><td align="left"><a href="admin.php?f=15" id="small_button">Clean keywords</a>
		 </td><td align="left"> Delete all keywords not associated with any link.</td></tr>
		<tr class="grey"  ><td align="left"><a href="admin.php?f=16" id="small_button">Clean links</a>
		</td><td align="left"> Delete all links not associated with any site.</td></tr>
		<tr class="grey"  ><td align="left"><a href="admin.php?f=17" id="small_button">Clear temp tables </a>
		</td><td align="left"> <?php print $temp;?> items in temporary table.</td></tr>
		<tr class="grey"  ><td align="left"><a href="admin.php?f=23" id="small_button">Clear search log </a>
		</td><td align="left"><?php print $log;?> items in search log.
		</td></tr></table>  	</td></tr></table></div>
		<?php
	}

	function statisticsForm($type) {
		global $log_dir;
		global $db;
		?>
		<div id='submenu'>
		<ul>
		<li><a href="admin.php?f=statistics&type=keywords">Top keywords</a></li>
		<li><a href="admin.php?f=statistics&type=pages">Largest pages</a></li>
		<li><a href="admin.php?f=statistics&type=top_searches">Most popular searches</a></li>
		<li><a href="admin.php?f=statistics&type=log">Search log</a></li>
		<li><a href="admin.php?f=statistics&type=spidering_log">Spidering logs</a></li>
		</ul>
		</div>

		<?php
			if ($type == "") {
				$cachedSumQuery = "select sum(length(fulltxt)) from ".TABLE_PREFIX."links";
				$result=$db->query("select sum(length(fulltxt)) from ".TABLE_PREFIX."links");
				echo sql_errorstring(__FILE__,__LINE__);
				if ($row=$result->fetch()) {
					$cachedSumSize = $row[0];
				}
				$cachedSumSize = number_format($cachedSumSize / 1024, 2);

				$sitesSizeQuery = "select sum(size) from ".TABLE_PREFIX."links";
				$result=$db->query("$sitesSizeQuery");
				echo sql_errorstring(__FILE__,__LINE__);
				if ($row=$result->fetch()) {
					$sitesSize = $row[0];
				}
				$sitesSize = number_format($sitesSize, 2);

				$stats = getStatistics();
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td><b>Sites:</b></td><td align=\"center\">".$stats['sites']."</td></tr>";
				print "<tr class=\"white\"><td><b>Links:</b></td><td align=\"center\"> ".$stats['links']."</td></tr>";
				print "<tr class=\"grey\"><td><b>Categories:</b></td><td align=\"center\"> ".$stats['categories']."</td></tr>";
				print "<tr class=\"white\"><td><b>Keywords:</b></td><td align=\"center\"> ".$stats['keywords']."</td></tr>";
				print "<tr class=\"grey\"><td><b>Keyword-link relations:</b></td><td align=\"center\"> ".$stats['index']."</td></tr>";
				print "<tr class=\"white\"><td><b>Cached texts total:</b></td><td align=\"center\"> $cachedSumSize kb</td></tr>";
				print "<tr class=\"grey\"><td><b>Sites size total:</b></td><td align=\"center\"> $sitesSize kb</td></tr>";
				print "</table></td></tr></table></div>";
			}

			if ($type=='keywords') {
				$class = "grey";
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td><b>Keyword</b></td><td><b>Occurrences</b></td></tr>";
				for ($i=0;$i<=15; $i++) {
					$char = dechex($i);
					$result=$db->query("select keyword, count(".TABLE_PREFIX."link_keyword$char.keyword_id) as x from ".TABLE_PREFIX."keywords, ".TABLE_PREFIX."link_keyword$char where ".TABLE_PREFIX."keywords.keyword_id = ".TABLE_PREFIX."link_keyword$char.keyword_id group by keyword order by x desc limit 30");
					echo sql_errorstring(__FILE__,__LINE__);
					while ($result && ($row=$result->fetch())) {
						$topwords[$row[0]] = $row[1];
					}
					$result->closeCursor();
				}
				arsort($topwords);
				$count = 0;
				foreach ($topwords as $word => $weight) {
					if (++$count > 15*30)
						break;
					if ($class =="white")
						$class = "grey";
					else
						$class = "white";

					print "<tr class=\"$class\"><td align=\"left\">".$word."</td><td> ".$weight."</td></tr>\n";
				}
				print "</table></td></tr></table></div>";
			}
			if ($type=='pages') {
				$class = "grey";
				?>
			<br/><div align="center">
			<table cellspacing ="0" cellpadding="0" class="darkgrey"><tr><td>
			<table cellpadding="2" cellspacing="1">
			  <tr class="grey"><td>
			   <b>Page</b></td>
			   <td><b>Text size</b></td></tr>
			<?php
				$result=$db->query("select ".TABLE_PREFIX."links.link_id, url, length(fulltxt)  as x from ".TABLE_PREFIX."links order by x desc limit 20");
				echo sql_errorstring(__FILE__,__LINE__);
				while ($row=$result->fetch()) {
					if ($class =="white")
						$class = "grey";
					else
						$class = "white";
					$url = $row[1];
					$sum = number_format($row[2]/1024, 2);
					print "<tr class=\"$class\"><td align=\"left\"><a href=\"$url\">".$url."</td><td align= \"center\"> ".$sum."kb</td></tr>";
				}
				print "</table></td></tr></table></div>";
			}

			if ($type=='top_searches') {
				$class = "grey";
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td><b>Query</b></td><td><b>Count</b></td><td><b> Average results</b></td><td><b>Last	queried</b></td></tr>";
				$result=$db->query("select query, count(*) as c, max(time), avg(results)  from ".TABLE_PREFIX."query_log group by query order by c desc");
				echo sql_errorstring(__FILE__,__LINE__);
				while ($row=$result->fetch()) {
					if ($class =="white")
						$class = "grey";
					else
						$class = "white";

					$word = utf8_decode($row[0]);
					$times = $row[1];
					$date = $row[2];
					$avg = number_format($row[3], 1);
					print "<tr class=\"$class\"><td align=\"left\">".htmlentities($word)."</td><td align=\"center\"> ".$times."</td><td align=\"center\"> ".$avg."</td><td align=\"center\"> ".$date."</td></tr>";
				}
				print "</table></td></tr></table></div>";
			}
			if ($type=='log') {
				$class = "grey";
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td align=\"center\"><b>Query</b></td><td align=\"center\"><b>Results</b></td><td	align=\"center\"><b>Queried	at</b></td><td	align=\"center\"><b>Time	taken</b></td></tr>";
				$result=$db->query("select query,  time, elapsed, results from ".TABLE_PREFIX."query_log order by time desc");
				echo sql_errorstring(__FILE__,__LINE__);
				while ($row=$result->fetch()) {
					if ($class =="white")
						$class = "grey";
					else
						$class = "white";

					$word = utf8_decode($row[0]);
					$time = $row[1];
					$elapsed = $row[2];
					$results = $row[3];
					print "<tr class=\"$class\"><td align=\"left\">".htmlentities($word)."</td><td align=\"center\"> ".$results."</td><td align=\"center\"> ".$time."</td><td align=\"center\"> ".$elapsed."</td></tr>";
				}
				print "</table></td></tr></table></div>";
			}

			if ($type=='spidering_log') {
				$class = "grey";
				$files = get_dir_contents($log_dir);
				if (count($files)>0) {
					print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td align=\"center\"><b>File</b></td><td align=\"center\"><b>Time</b></td><td	align=\"center\"><b></b></td></tr>";

					for ($i=0; $i<count($files); $i++) {
						$file=$files[$i];
						$year = substr($file, 0,2);
						$month = substr($file, 2,2);
						$day = substr($file, 4,2);
						$hour = substr($file, 6,2);
						$minute = substr($file, 8,2);
						if ($class =="white")
							$class = "grey";
						else
							$class = "white";
						print "<tr class=\"$class\"><td align=\"left\"><a href='$log_dir/$file' tareget='_blank'>$file</a></td><td align=\"center\"> 20$year-$month-$day $hour:$minute</td><td align=\"center\"> <a href='?f=delete_log&file=$file' id='small_button'>Delete</a></td></tr>";
					}

					print "</table></td></tr></table></div>";
				} else {
					?>
				<br/><br/>
				<center><b>No saved logs.</b></center>
				<?php
				}
			}

	}

        if (!isset($site_id))
            $site_id = isset($_GET["site_id"]) ? $_GET["site_id"] : "";
        if (!isset($url))
            $url = isset($_GET["url"]) ? $_GET["url"] : "";
        if (!isset($reindex))
            $reindex = isset($_GET["reindex"]) ? $_GET["reindex"] : "";

	switch ($f) {
	case 1:
		$message = addsite($url, $title, $short_desc, $cat);
		$compurl=parse_url($url);
		if ($compurl['path']=='')
			$url=$url."/";

		$result = $db->query("select site_id from ".TABLE_PREFIX."sites where url='$url'");
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		if ($site_id != "")
			siteScreen($site_id, $message);
		else
			showsites($message);
		break;
	case 2:
		showsites();
		break;
	case 'edit_site':
		if (CONFIGSET)
			editsiteform($site_id);
		break;
	case 4:
		if (!isset($domaincb))
			$domaincb = 0;
		if (!isset($cat))
			$cat = "";
		if ($soption =='full') {
			$depth = -1;
		}
		$message = editsite ($site_id, $url, $title, $short_desc, $depth, $in, $out,  $domaincb, $cat);
		showsites($message);
		break;
	case 5:
		deletesite ($site_id);
		showsites();
		break;
	case 'add_cat':
		if (!isset($parent))
			$parent = "";
		addcatform ($parent);
		break;
	case 7:
		if (!isset($parent)) {
			$parent = "";
		}
		$message = addcat ($category, $parent);
		list_cats (0, 0, "white", $message);
		break;
	case 'categories':
		list_cats (0, 0, "white", "");
		break;
	case 'edit_cat':
		editcatform($cat_id);
		break;
	case 10:
		$message = editcat ($cat_id, $category);
		list_cats (0, 0, "white", $message);
		break;
	case 11:
		deletecat($cat_id);
		list_cats (0, 0, "white");
		break;
	case 'index':
		if (isset($_GET["adv"]))
			$_SESSION['index_advanced']=$_GET["adv"];
		indexscreen($url, $reindex);
		break;
	case 'add_site':
		addsiteform();
		break;
	case 'clean':
		cleanForm();
		break;

	case 15:
		cleanKeywords();
		break;
	case 16:
		cleanLinks();
		break;

	case 17:
		cleanTemp();
		break;

	case 'statistics':
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		statisticsForm($type);
		break;

	case 19:
		siteStats($site_id);
		break;
	case 20:
		siteScreen($site_id);
		break;
	case 21:
		if (!isset($start))
			$start = 1;
		if (!isset($filter))
			$filter = "";
		if (!isset($per_page))
			$per_page = 10;

		browsePages($site_id, $start, $filter, $per_page);
		break;
	case 22:
		deletePage($link_id);
		if (!isset($start))
			$start = 1;
		if (!isset($filter))
			$filter = "";
		if (!isset($per_page))
			$per_page = 10;
		browsePages($site_id, $start, $filter, $per_page);
		break;
	case 23:
		clearLog();
		break;
	case 24:
		session_destroy();
		header("Location: admin.php");
		break;
	case 'database':
		include_once("db_main.php");
		break;
	case 'settings':
		if (CONFIGSET)
			include_once('configset.php');
		break;
	case 'delete_log':
		unlink($log_dir."/".$file);
		statisticsForm('spidering_log');
		break;
	case '':
		showsites();
		break;
	}
	$stats = getStatistics();
	print "<br/><br/>   <center>Currently in database: ".$stats['sites']." sites, ".$stats['links']." links, ".$stats['categories']." categories and ".$stats['keywords']." keywords.<br/><br/></center>\n";

?>
</div>
</div>
</body>
</html>