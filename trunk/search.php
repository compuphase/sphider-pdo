<?php
/*******************************************
* Sphider Version 1.3.x
* This program is licensed under the GNU GPL.
* By Ando Saabas          ando(a t)cs.ioc.ee
********************************************/
error_reporting (E_ALL | E_STRICT);

$include_dir = "./include";
$template_dir = "./templates";
$settings_dir = "./settings";
$language_dir = "./languages";

include ("$include_dir/commonfuncs.php");
//extract(getHttpVars());

require_once("$settings_dir/database.php");
require_once("$include_dir/searchfuncs.php");
require_once("$include_dir/categoryfuncs.php");

include "$settings_dir/conf.php";

// http://www.sphider.eu/forum/read.php?2,9135 (modified for PDO)
if (isset($_GET['query']))
	$query = quotestring($_GET['query']);
if (isset($_GET['search']))
	$search = quotestring($_GET['search']);
if (isset($_GET['domain']))
	$domain = quotestring($_GET['domain']);
if (isset($_GET['type']))
	$type = quotestring($_GET['type']);
if (isset($_GET['catid']))
	$catid = quotestring($_GET['catid']);
if (isset($_GET['category']))
	$category = quotestring($_GET['category']);
if (isset($_GET['results']))
	$results = quotestring($_GET['results']);
if (isset($_GET['start']))
	$start = quotestring($_GET['start']);
if (isset($_GET['adv']))
	$adv = quotestring($_GET['adv']);
if (isset($_GET['lang']))
	$language = $_GET['lang'];

require_once("$language_dir/$language-language.php");
require_once "$template_dir/$template/header_$language.html";


if (!isset($type) || ($type != "or" && $type != "and" && $type != "phrase")) {
	$type = "and";
}

if (!isset($domain) || preg_match("/[^a-z0-9-.]+/", $domain)) {
	$domain="";
}


if (isset($results) && $results != "") {
	$results_per_page = $results;
}

if (get_magic_quotes_gpc()==1) {
	$query = stripslashes($query);
}

if (!isset($catid) || !is_numeric($catid)) {
	$catid = "";
}

if (!isset($category) || !is_numeric($category)) {
	$category = "";
}



if ($catid && is_numeric($catid)) {
	$tpl_['category'] = sql_fetch_all('SELECT category FROM '.$table_prefix.'categories WHERE category_id='.(int)$_REQUEST['catid']);
}

$count_level0 = sql_fetch_all('SELECT count(*) FROM '.$table_prefix.'categories WHERE parent_num=0');
$has_categories = 0;

if ($count_level0) {
	$has_categories = $count_level0[0][0];
}



require_once("$template_dir/$template/search_form.html");


function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
    }



function poweredby () {
	global $sph_messages;
    //If you want to remove this, please donate to the project at http://www.sphider.eu/donate.php
    print $sph_messages['Powered by'];?>  <a href="http://www.sphider.eu/"><img src="sphider-logo.png" border="0" style="vertical-align: middle" alt="Sphider"></a>

    <?php
}


function saveToLog ($query, $elapsed, $results) {
        global $table_prefix;
        global $db;
        global $search;
        global $start;

    if ($results =="") {
        $results = 0;
    }
    $query =  "insert into ".$table_prefix."query_log (query, time, elapsed, results) values ('$query', DATETIME('NOW'), '$elapsed', '$results')";
	$db->exec($query);
	echo sql_errorstring(__FILE__,__LINE__);
}

if (!isset($search))
    $search = 0;
if (!isset($start))
    $start = 0;
switch ($search) {
	case 1:

		if (!isset($results)) {
			$results = "";
		}
		$search_results = get_search_results($query, $start, $category, $type, $results, $domain);
		require("$template_dir/$template/search_results.html");
	break;
	default:
		if ($show_categories) {
			if (isset($_REQUEST['catid']) && $_REQUEST['catid'] && is_numeric($catid)) {
				$cat_info = get_category_info($catid);
			} else {
				$cat_info = get_categories_view();
			}
			require("$template_dir/$template/categories.html");
		}
	break;
	}

include "$template_dir/$template/footer.html";
?>
