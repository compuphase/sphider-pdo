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

require_once("$settings_dir/database.php");
require_once("$include_dir/searchfuncs.php");
require_once("$include_dir/categoryfuncs.php");

include "$settings_dir/conf.php";

if (isset($_GET['query']))
	$query = sanitize($_GET['query']);
if (isset($_GET['search']))
	$search = sanitize($_GET['search']);
if (isset($_GET['domain']))
	$domain = sanitize($_GET['domain']);
if (isset($_GET['type']))
	$type = sanitize($_GET['type']);
if (isset($_GET['catid']))
	$catid = sanitize($_GET['catid']);
if (isset($_GET['category']))
	$category = sanitize($_GET['category']);
if (isset($_GET['results']))
	$results = sanitize($_GET['results']);
if (isset($_GET['start']))
	$start = sanitize($_GET['start']);
if (isset($_GET['adv']))
	$adv = sanitize($_GET['adv']);
if (isset($_GET['lang']))
	$language = $_GET['lang'];

if (strcasecmp($query, $searchprompt) == 0)
	$query = "";

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

if (isset($query) && get_magic_quotes_gpc()==1) {
	$query = stripslashes($query);
}

if (!isset($catid) || !is_numeric($catid)) {
	$catid = "";
}

if (!isset($category) || !is_numeric($category)) {
	$category = "";
}


if ($catid && is_numeric($catid)) {
	$tpl_['category'] = sql_fetch_all('SELECT category FROM '.TABLE_PREFIX.'categories WHERE category_id=:catid', array(':catid' => (int)$_REQUEST['catid']));
}

$count_level0 = sql_fetch_all('SELECT count(*) FROM '.TABLE_PREFIX.'categories WHERE parent_num=:parent', array(':parent' => 0));
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
	global $db;

    if ($results =="")
		$results = 0;
	$stat = $db->prepare("insert into ".TABLE_PREFIX."query_log (query, time, elapsed, results) values (:query, DATETIME('NOW'), :elapsed, :results)");
	$stat->execute(array(':query' => $query, ':elapsed' => $elapsed, ':results' => $results));
}

if (!isset($search) || strlen($query) == 0)
    $search = 0;
if (!isset($start))
    $start = 0;
switch ($search) {
	case 1:
		if (!isset($results))
			$results = "";
		if ($type != "phrase") {
			$query = str_replace("\"", " ", $query);
			$query = str_replace("&quot;", " ", $query);
			$query = str_replace("&#39;", " ", $query);
		}
		$query = str_replace("&amp;", " ", $query);
		$query = str_replace("&lt;", " ", $query);
		$query = str_replace("&gt;", " ", $query);
		$query = str_replace("#", " ", $query);
		$query = str_replace("&", " ", $query);
		$query = str_replace(";", " ", $query);
		$query = str_replace("'", " ", $query);
		$query = str_replace("*", " ", $query);
		$query = str_replace("%", " ", $query);
		$query = str_replace("\\", " ", $query);
		if (strpos($query, '\0') != FALSE)
			$query = "";
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
