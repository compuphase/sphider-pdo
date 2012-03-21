<?php
	$database = dirname(__FILE__)."/../sphider.db";
	$table_prefix="";

	$db = new PDO('sqlite:'.$database);
	// $db = new PDO('mysql:host=hostname;dbname=$database','username','password');

    function quotestring($str)
    {
        $search  = array('"'     , "'"    , '<'   , '>'    );
        $replace = array('&quot;', '&#39;', '&lt;', '&gt;' );
        $str = str_replace($search, $replace, $str);
        return $str;
    }
?>
