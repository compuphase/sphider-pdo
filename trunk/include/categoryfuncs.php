<?php

function get_categories_view() {
    global $db;
    $result = $db->query('SELECT * FROM '.TABLE_PREFIX.'categories WHERE parent_num=0 ORDER BY category');
	$categories['main_list'] = $result->fetchAll();

	if (is_array($categories['main_list'])) {
		foreach ($categories['main_list'] as $_key => $_val) {
            $result = $db->query('SELECT * FROM '.TABLE_PREFIX.'categories WHERE parent_num='.$_val['category_id']);
			$categories['main_list'][$_key]['sub'] =  $result->fetchAll();
		}
	}
	return $categories;
}

function get_category_info($catid) {
    global $db;
	$result = $db->query("SELECT * FROM ".TABLE_PREFIX."categories ORDER BY category");
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
            $result = $db->query('SELECT count(*) FROM '.TABLE_PREFIX.'site_category WHERE 	category_id='.(int)$_val['category_id']);
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
    $result = $db->query('SELECT url, title, short_desc FROM '.TABLE_PREFIX.'sites, '.TABLE_PREFIX.'site_category WHERE category_id='.$catid.' AND '.TABLE_PREFIX.'sites.site_id='.TABLE_PREFIX.'site_category.site_id order by title');
	$categories['cat_sites'] = $result->fetchAll();

	return $categories;
}


?>