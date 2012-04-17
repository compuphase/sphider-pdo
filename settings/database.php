<?php
	$db = new PDO('sqlite:'.dirname(__FILE__)."/../sphider.sqlite);
	// $database = 'sphider';
	// $db = new PDO('mysql:host=hostname;dbname=$database','username','password');
	$table_prefix="";

	function quotestring($str) {
		$search  = array('"'     , "'"    , '<'   , '>'   , '\0' );
		$replace = array('&quot;', '&#39;', '&lt;', '&gt;', ''   );
		$str = str_replace($search, $replace, $str);
		return $str;
	}
?>
