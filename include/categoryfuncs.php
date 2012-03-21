<?php

function get_categories_view() {
	global $table_prefix;
    global $db;
    $result = $db->query('SELECT * FROM '.$table_prefix.'categories WHERE parent_num=0 ORDER BY category');
	$categories['main_list'] = $result->fetchAll();

	if (is_array($categories['main_list'])) {
		foreach ($categories['main_list'] as $_key => $_val) {
            $result = $db->query('SELECT * FROM '.$table_prefix.'categories WHERE parent_num='.$_val['category_id']);
			$categories['main_list'][$_key]['sub'] =  $result->fetchAll();
		}
	}
	return $categories;
}

function get_category_info($catid) {
	global $table_prefix;
    global $db;
	$result = $db->query("SELECT * FROM ".$table_prefix."categories ORDER BY category");
    $categories['main_list'] = $result->fetchAll();

	if (is_array($categories['main_list'])) {
		foreach($categories['main_list'] as $_val) {
			$categories['categories'][$_val['category_id']] = $_val;
			$categories['subcats'][$_val['parent_num']][] = $_val;
		}
	}

	$categories['subcats'] = $categories['subcats'][$_REQUEST['catid']];

	/* count sites */
	if (is_array($categories['subcats'])) {
		foreach ($categories['subcats'] as $_key => $_val) {
            $result = $db->query('SELECT count(*) FROM '.$table_prefix.'site_category WHERE 	category_id='.(int)$_val['category_id']);
			$categories['subcats'][$_key]['count'] = $result->fetchAll();
		}
	}

	/* make tree */
	$_parent = $catid;
	while ($_parent) {
		$categories['cat_tree'][] = $categories['categories'][$_parent];
		$_parent = $categories['categories'][$_parent]['parent_num'];
	}
	$categories['cat_tree'] = array_reverse($categories['cat_tree']);


	/* list category sites */
    $result = $db->query('SELECT url, title, short_desc FROM '.$table_prefix.'sites, '.$table_prefix.'site_category WHERE category_id='.$catid.' AND '.$table_prefix.'sites.site_id='.$table_prefix.'site_category.site_id order by title');
	$categories['cat_sites'] = $result->fetchAll();

	return $categories;
}


?>