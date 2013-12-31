<?php
/*******************************************
* Sphider Version 1.3.x
* This program is licensed under the GNU GPL.
* By Ando Saabas		  ando(a t)cs.ioc.ee
********************************************/

error_reporting (E_ALL | E_STRICT);

require_once "$include_dir/double_metaphone.php";

	function swap_max (&$arr, $start, $domain) {
		$pos  = $start;
		$maxweight = $arr[$pos]['weight'];
		for  ($i = $start; $i< count($arr); $i++) {
			if ($arr[$i]['domain'] == $domain) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
				break;
			}
			if ($arr[$i]['weight'] > $maxweight) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
			}
		}
		$temp = $arr[$start];
		$arr[$start] = $arr[$pos];
		$arr[$pos] = $temp;
	}

	function sort_with_domains (&$arr) {
		$domain = -1;
		for  ($i = 0; $i< count($arr)-1; $i++) {
			swap_max($arr, $i, $domain);
			$domain = $arr[$i]['domain'];
		}
	}

	function cmp($a, $b) {
		$wa = isset($a['weight']) ? $a['weight'] : 0;
		$wb = isset($b['weight']) ? $b['weight'] : 0;
		if ($wa == $wb)
			return 0;
		return ($wa > $wb) ? -1 : 1;
	}

	function addmarks($a) {
		$a = preg_replace("/[ ]+/", " ", $a);
		$a = str_replace(" +", "+", $a);
		$a = str_replace(" ", "+", $a);
		return $a;
	}

	function makeboollist($a) {
		global $stem_words;
		$a = utf8_decode($a);     /* if words are passed as UTF8, translate to Latin-1 */
		$a = html_to_latin1($a);  /* if any words are passed as HTML entities, translate to Latin-1 */
		$a = trim($a);

		$a = preg_replace("/&quot;/", "\"", $a);
		$returnWords = array();
		//get all phrases
		$regs = Array();
		while (preg_match("/([-]?)\"([^\"]+)\"/", $a, $regs)) {
			if ($regs[1] == '') {
				$returnWords['+s'][] = $regs[2];
				$returnWords['hilight'][] = $regs[2];
			} else {
				$returnWords['-s'][] = $regs[2];
			}
			$a = str_replace($regs[0], "", $a);   /* remove the phrase from the search string */
		}
		$a = strtolower(preg_replace("/[ ]+/", " ", $a)); /* replace multiple spaces by a single one, and convert to lower case */
		$a = trim($a);  		  /* erase leading and trailing spaces */
		$words = explode(' ', $a);
		if ($a=="") {
			$limit = 0;
		} else {
			$limit = count($words);
		}

		$k = 0;
		//get all words (both include and exlude)
		$includeWords = array();
		while ($k < $limit) {
			if (substr($words[$k], 0, 1) == '+') {
				$includeWords[] = substr($words[$k], 1);
				if (!ignoreWord(substr($words[$k], 1))) {
					$returnWords['hilight'][] = substr($words[$k], 1);
					if ($stem_words == 1) {
						$returnWords['hilight'][] = stem(substr($words[$k], 1));
					}
				}
			} else if (substr($words[$k], 0, 1) == '-') {
				$returnWords['-'][] = substr($words[$k], 1);
			} else {
				$includeWords[] = $words[$k];
				if (!ignoreWord($words[$k])) {
					$returnWords['hilight'][] = $words[$k];
					if ($stem_words == 1) {
						$returnWords['hilight'][] = stem($words[$k]);
					}
				}
			}
			$k++;
		}
		//add words from phrases to includes
		if (isset($returnWords['+s'])) {
			foreach ($returnWords['+s'] as $phrase) {
				$phrase = strtolower(preg_replace("/[ ]+/", " ", $phrase));
				$phrase = trim($phrase);
				$temparr = explode(' ', $phrase);
				foreach ($temparr as $w)
					$includeWords[] = $w;
			}
		}

		foreach ($includeWords as $word) {
			if (!($word =='')) {
				if (ignoreWord($word)) {
					$returnWords['ignore'][] = $word;
				} else {
					$returnWords['+'][] = $word;
				}
			}

		}
		return $returnWords;

	}

	function ignoreword($word) {
		global $common;
		global $min_word_length;
		global $index_numbers;
		if ($index_numbers == 1) {
			$pattern = "[a-z0-9]+";
		} else {
			$pattern = "[a-z]+";
		}
		if (strlen($word) < $min_word_length || !preg_match("/".$pattern."/i", remove_accents($word)) || (isset($common[$word]) && $common[$word] == 1)) {
			return 1;
		} else {
			return 0;
		}
	}

	function search($searchstr, $category, $start, $per_page, $type, $domain) {
		global $length_of_link_desc, $show_meta_description, $merge_site_results, $stem_words;
		global $did_you_mean_enabled,$did_you_mean_always;
		global $matchless,$equivalent,$language;
		global $db;
		$possible_to_find = 1;
        $stat = $db->prepare("SELECT domain_id FROM ".TABLE_PREFIX."domains WHERE domain = :domain");
		$stat->execute(array(':domain' => $domain));
		if ($row = $stat->fetch()) {
			$domain_qry = "and domain = ".$row[0];
		} else {
			$domain_qry = "";
		}
		$stat->closeCursor();

		/* if there are no words to search for, quit */
		if (!isset($searchstr['+']) || count($searchstr['+']) == 0)
			return null;
		/* find all words that should _not_ be included in the result */
		if (isset($searchstr['-']))
			$wordarray = $searchstr['-'];
		else
			$wordarray = array();
		$notlist = array();
		$not_words = 0;
		while ($not_words < count($wordarray)) {
			if ($stem_words == 1)
				$searchword = stem($wordarray[$not_words]);
			else
				$searchword = $wordarray[$not_words];
			$wordmd5 = substr(md5($searchword), 0, 1);

			$stat = $db->prepare("SELECT link_id from ".TABLE_PREFIX."link_keyword$wordmd5, ".TABLE_PREFIX."keywords where ".TABLE_PREFIX."link_keyword$wordmd5.keyword_id= ".TABLE_PREFIX."keywords.keyword_id and keyword = :keyword");
			$stat->execute(array(':keyword' => $searchword));
			while ($row = $stat->fetch())
				$notlist[$not_words]['id'][$row[0]] = 1;
			$not_words++;
		}

		/* find all phrases */
		if (isset($searchstr['+s']))
			$wordarray = $searchstr['+s'];
		else
			$wordarray = array();
		$phrase_words = 0;
		while ($phrase_words < count($wordarray)) {
			$searchword = $wordarray[$phrase_words];
			$searchword = str_replace("|", "", $searchword);
			$searchword = str_replace("%", "|%", $searchword);
			$searchword = str_replace("_", "|_", $searchword);
			$stat = $db->prepare("SELECT link_id from ".TABLE_PREFIX."links where fulltxt like :keyword escape '|'");
			$stat->execute(array(':keyword' => "%".$searchword."%"));
			echo sql_errorstring(__FILE__,__LINE__);
			$row = $stat->fetch();
			if (! $row) {
				$possible_to_find = 0;
				$stat->closeCursor();
				break;
			}
			$phraselist[$phrase_words]['id'][$row[0]] = 1;
			while ($row = $stat->fetch()) {
				$phraselist[$phrase_words]['id'][$row[0]] = 1;
			}
			$phrase_words++;
		}

		if ($category> 0 && $possible_to_find==1) {
			$allcats = get_cats($category);
			$catlist = implode(",", $allcats);
			$result = $db->query("SELECT link_id FROM ".TABLE_PREFIX."links, ".TABLE_PREFIX."sites, ".TABLE_PREFIX."categories, ".TABLE_PREFIX."site_category where ".TABLE_PREFIX."links.site_id = ".TABLE_PREFIX."sites.site_id and ".TABLE_PREFIX."sites.site_id = ".TABLE_PREFIX."site_category.site_id and ".TABLE_PREFIX."site_category.category_id in ($catlist)");
			echo sql_errorstring(__FILE__,__LINE__);
			$row = $result->fetch();
			if (! $row) {
				$possible_to_find = 0;
			} else {
				$category_list[$row[0]] = 1;
				while ($row = $result->fetch())
					$category_list[$row[0]] = 1;
			}
            $result->closeCursor();
		}

		/* find individual words */
		$word_not_found = array();
		$wordarray = $searchstr['+'];
		$words = 0;
		while (($words < count($wordarray)) && $possible_to_find == 1) {
			if ($stem_words == 1)
				$searchword = stem($wordarray[$words]);
			else
				$searchword = $wordarray[$words];
			$wordmd5 = substr(md5($searchword), 0, 1);
			$stat = $db->prepare("SELECT distinct link_id, weight, domain FROM ".TABLE_PREFIX."link_keyword$wordmd5, ".TABLE_PREFIX."keywords WHERE ".TABLE_PREFIX."link_keyword$wordmd5.keyword_id= ".TABLE_PREFIX."keywords.keyword_id AND keyword=:keyword $domain_qry	ORDER	BY	weight	DESC");
			$stat->execute(array(':keyword' => $searchword));
			echo sql_errorstring(__FILE__,__LINE__);
			$row = $stat->fetch();
			if (! $row) {
				$word_not_found[$wordarray[$words]] = 1;
				if ($type != "or") {
					$possible_to_find = 0;
					$stat->closeCursor();
					break;
				}
			}
			if ($type == "or") {
				$indx = 0;
			} else {
				$indx = $words;
			}

			do {
				$linklist[$indx]['id'][] = $row[0];
				$domains[$row[0]] = $row[2];
				$linklist[$indx]['weight'][$row[0]] = $row[1];
			} while ($row = $stat->fetch());
			$words++;
		}

		if ($type == "or")
			$words = 1;
		$result_array_full = Array();

		if ($possible_to_find != 0) {
			if ($words == 1 && $not_words == 0 && $category < 1) { //if there is only one search word, we already have the result
				$result_array_full = $linklist[0]['weight'];
			} else { //otherwise build an intersection of all the results
				$j= 1;
				$min = 0;
				while ($j < $words) {
					if (count($linklist[$min]['id']) > count($linklist[$j]['id'])) {
						$min = $j;
					}
					$j++;
				}

				$j = 0;
				$temp_array = $linklist[$min]['id'];
				$count = 0;
				while ($j < count($temp_array)) {
					$k = 0; //and word counter
					$n = 0; //not word counter
					$o = 0; //phrase word counter
					$weight = 1;
					$break = 0;
					while ($k < $words && $break== 0) {
						if (isset($linklist[$k]['weight'][$temp_array[$j]]) && $linklist[$k]['weight'][$temp_array[$j]] > 0) {
							$weight = $weight + $linklist[$k]['weight'][$temp_array[$j]];
						} else {
							$break = 1;
						}
						$k++;
					}
					while ($n < $not_words && $break== 0) {
						if ($notlist[$n]['id'][$temp_array[$j]] > 0) {
							$break = 1;
						}
						$n++;
					}

					while ($o < $phrase_words && $break== 0) {
						if (!isset($phraselist[$n]['id'][$temp_array[$j]]) || $phraselist[$n]['id'][$temp_array[$j]] != 1) {
							$break = 1;
						}
						$o++;
					}
					if ($break== 0 && $category > 0 && $category_list[$temp_array[$j]] != 1) {
						$break = 1;
					}

					if ($break == 0) {
						$result_array_full[$temp_array[$j]] = $weight;
						$count ++;
					}
					$j++;
				}
			}
		}

		if ((count($result_array_full) == 0 || $possible_to_find == 0 || $did_you_mean_always == 1) && $did_you_mean_enabled == 1) {
			/* search for word pairs written as two words where a single words
			   for example: when the user typed "full colour", also search for
			   fullcolour and full-colour */
			for ($idx = 0; $idx < count($searchstr['+']) - 1; $idx++) {
				$word = $searchstr['+'][$idx] . " " . $searchstr['+'][$idx+1];
				$near_word = $searchstr['+'][$idx] . $searchstr['+'][$idx+1];
				/* words that are in the "nonpareil" list are excluded in searching
				   for alternatives */
				if (!isset($matchless[$near_word])) {
					$stat = $db->prepare("SELECT keyword FROM ".TABLE_PREFIX."keywords WHERE keyword=:keyword");
					if ($stat->execute(array(':keyword' => $near_word)) && $row=$stat->fetch()) {
						$near_words[$word] = latin1_to_html($near_word);
						$stat->closeCursor();
					}
				}
				$near_word = $searchstr['+'][$idx] . "-" . $searchstr['+'][$idx+1];
				if (!isset($matchless[$near_word])) {
					$stat = $db->prepare("SELECT keyword FROM ".TABLE_PREFIX."keywords WHERE keyword=:keyword");
					if ($stat->execute(array(':keyword' => $near_word)) && $row=$stat->fetch()) {
						$near_words[$word] = latin1_to_html($near_word);
						$stat->closeCursor();
					}
				}
			}
			/* then search for "near words" for the individual words */
			reset($searchstr['+']);
			foreach ($searchstr['+'] as $word) {
				/* words that are in the "nonpareil" list are excluded in searching
				   for alternatives */
				if (isset($matchless[$word]) && $matchless[$word] == 1)
					continue;
				/* search for alternatives in the explicit equivalents word list first */
				if (isset($equivalent[$word]) && strlen($equivalent[$word]) > 0) {
					$near_words[$word] = latin1_to_html($equivalent[$word]);
					continue;
				}
				/* if there are misspelled words, show only alternatives for the
				   misspelled words, (so, if the current word is not in the list
				   of misspelled words, exclude it from the search for alternatives */
				if (count($word_not_found) > 0 && !(isset($word_not_found[$word]) && $word_not_found[$word] == 1))
					continue;
				$word = sanitize($word);
				/* use the double-metaphone to find close words */
				$meta = double_metaphone($word);
				if (!isset($meta["primary"]) || strlen($meta["primary"]) == 0)
					continue; /* no metaphone, don't match anything */
				$where = "metaphone1='".$meta["primary"]."' OR metaphone2='".$meta["primary"]."'";
				if (isset($meta["secondary"]) && strlen($meta["secondary"]) > 0)
					$where .= " OR metaphone1='".$meta["secondary"]."' OR metaphone2='".$meta["secondary"]."'";
				$result = $db->query("SELECT keyword FROM ".TABLE_PREFIX."keywords WHERE $where");
				/* adapted from http://www.mdj.us/web-development/php-programming/creating-better-search-suggestions-with-sphider/
				   but using a double-metaphone filter (instead of SOUNDEX) and
				   adding a filter for accented characters */
				$max_distance = 3;
				$max_similar = 0;
				$near_word = "";
				while ($result && $row=$result->fetch()) {
					$item = $row[0];
					if (strcasecmp($item, $word) != 0) {
						$distance = levenshtein($item, $word);
						$distance_na = levenshtein(remove_accents($item), $word);
						if ($distance_na < $distance)
							$distance = $distance_na;
						if ($distance < $max_distance) {
							$max_distance = $distance;
							$near_word = $item;
						}
						if ($distance == $max_distance) {
							$similar = similar_text($item, $word);
							if ($similar >= $max_similar) {
								$max_distance = $distance;
								$max_similar = $similar;
								$near_word = $item;
							}
						}
					}
				}
				if ($near_word != "")
					$near_words[$word] = latin1_to_html($near_word);
				else if (isset($word_not_found[$word]) && $word_not_found[$word] == 1 && count($wordarray) > 1)
					$near_words[$word] = "/$word";
			}
			if (!isset($near_words))
				$near_words = "";
			$res['did_you_mean'] = $near_words;
			if (count($result_array_full) == 0 || $possible_to_find == 0)
				return $res;
		}
		if (count($result_array_full) == 0) {
			return null;
		}
		arsort ($result_array_full);

		if ($merge_site_results == 1 && $domain_qry == "") {
			while (list($key, $value) = each($result_array_full)) {
				if (!isset($domains_to_show[$domains[$key]])) {
					$result_array_temp[$key] = $value;
					$domains_to_show[$domains[$key]] = 1;
				} else if ($domains_to_show[$domains[$key]] ==  1) {
					$domains_to_show[$domains[$key]] = Array ($key => $value);
				}
			}
		} else {
			$result_array_temp = $result_array_full;
		}


		while (list($key, $value) = each ($result_array_temp)) {
			$result_array[$key] = $value;
			if (isset ($domains_to_show[$domains[$key]]) && $domains_to_show[$domains[$key]] != 1) {
				list ($k, $v) = each($domains_to_show[$domains[$key]]);
				$result_array[$k] = $v;
			}
		}

		$results = count($result_array);

		$keys = array_keys($result_array);
		$maxweight = $result_array[$keys[0]];

		for ($i = ($start -1)*$per_page; $i <min($results, ($start -1)*$per_page + $per_page) ; $i++) {
			$in[] = $keys[$i];

		}
		if (!is_array($in)) {
			$res['results'] = $results;
			return $res;
		}

		$inlist = implode(",", $in);

		if ($length_of_link_desc == 0) {
			$fulltxt = "fulltxt";
		} else {
			$fulltxt = "substring(fulltxt, 1, $length_of_link_desc)";
		}

		$query = "SELECT distinct link_id, url, title, description, language, $fulltxt, size FROM ".TABLE_PREFIX."links WHERE link_id in ($inlist)";
		$result = $db->query($query);
		echo sql_errorstring(__FILE__,__LINE__);

		$i = 0;
		while ($row = $result->fetch()) {
			$res[$i]['title'] = $row[2];
			$res[$i]['url'] = $row[1];
			if (isset($row[3]) && $row[3] != null && $show_meta_description == 1)
				$res[$i]['summary'] = $row[3];
			else
				$res[$i]['summary'] = "";
			$res[$i]['lang'] = $row[4];
			$res[$i]['fulltxt'] = $row[5];
			$res[$i]['size'] = $row[6];
			$res[$i]['weight'] = $result_array[$row[0]];
			/* if a language has been set for this page, and it is _not_ the
			 * same language as the user language, decrease the weight
			 */
			if (isset($row[4]) && $row[4] != null && strlen($row[4]) > 0 && strcasecmp($row[4], $language) != 0) {
				$res[$i]['weight'] *= 0.5;
			}
			$dom_result = $db->query("select domain from ".TABLE_PREFIX."domains where domain_id='".$domains[$row[0]]."'");
			$dom_row = $dom_result->fetch();
			$res[$i]['domain'] = $dom_row[0];
			$i++;
		}

		if ($merge_site_results  && $domain_qry == "") {
			sort_with_domains($res);
		} else {
			usort($res, "cmp");
		}
		echo sql_errorstring(__FILE__,__LINE__);
		/* sorting destroys the other columns in the array, restore */
		if (isset($near_words))
			$res['did_you_mean'] = $near_words;
		$res['maxweight'] = $maxweight;
		$res['results'] = $results;
		return $res;
	/**/
	}

function get_search_results($query, $start, $category, $searchtype, $results, $domain) {
	global $sph_messages, $results_per_page, $links_to_next, $show_query_scores, $desc_length;
	if ($results != "")
		$results_per_page = $results;

	if ($searchtype == "phrase") {
	   $query=str_replace('"','',$query);
	   $query = "\"".$query."\"";
	}

	$starttime = getmicrotime();
	// catch " if only one time entered
	$query = preg_replace("/&quot;/", "\"", $query);
	if (substr_count($query,'"')==1) {
	   $query=str_replace('"','',$query);
	}
	$words = makeboollist($query);
	if (isset($words['ignore']))
	  $ignorewords = $words['ignore'];
	else
	  $ignorewords = "";
	$full_result['ignore_words'] = $ignorewords;

	if ($start==0)
		$start=1;
	$result = search($words, $category, $start, $results_per_page, $searchtype, $domain);
	$query= stripslashes($query);

	$entitiesQuery = htmlspecialchars($query);
	$full_result['ent_query'] = $entitiesQuery;

	$endtime = getmicrotime() - $starttime;
	if (isset($result['results']))
		$rows = $result['results'];
	else
		$rows = "";
	$time = round($endtime*100)/100;

	$full_result['time'] = $time;

	$did_you_mean = array();
	$did_you_mean_b = array();

	if (isset($result['did_you_mean']) && is_array($result['did_you_mean'])) {
		while (list($key, $alt) = each($result['did_you_mean'])) {
			$entities = html_to_latin1(utf8_decode($entitiesQuery));
			if ($key != $alt) {
				$alt = html_to_latin1(utf8_decode($alt));
				$alt = sanitize($alt);
				$entities = preg_replace("/&quot;/", "\"", $entities);
				if ($alt[0] == "/") {	/* this indicates that the search word is not found and there is no close alternative either */
					$alt = substr($alt, 1);
					$did_you_mean_b[] = latin1_to_html(str_ireplace($key, "<strike>$alt</strike>", $entities));
					$did_you_mean[] = str_ireplace($key, "", $entities);
				} else {
					$did_you_mean_b[] = latin1_to_html(str_ireplace($key, "<b>$alt</b>", $entities));
					$did_you_mean[] = str_ireplace($key, utf8_encode($alt), $entities);
				}
			}
		}
	}

	$full_result['did_you_mean'] = $did_you_mean;
	$full_result['did_you_mean_b'] = $did_you_mean_b;

	$matchword = $sph_messages["matches"];
	if ($rows == 1) {
		$matchword= $sph_messages["match"];
	}

	$num_of_results = count($result) - 2;

	$full_result['num_of_results'] = $num_of_results;

	if ($start < 2)
		saveToLog($query, $time, $rows);
	$from = ($start-1) * $results_per_page+1;
	$to = min(($start)*$results_per_page, $rows);

	$full_result['from'] = $from;
	$full_result['to'] = $to;
	$full_result['total_results'] = $rows;
	if ($rows>0) {
		$maxweight = $result['maxweight'];
		$i = 0;
		while ($i < $num_of_results && $i < $results_per_page) {
			if (!isset($result[$i]['url'])) {
				$i++;
				continue;
			}
			$url = $result[$i]['url'];
			$title = isset($result[$i]['title']) ? $result[$i]['title'] : "";
			$summary = $result[$i]['summary'];
			$lang = $result[$i]['lang'];
			$fulltxt = $result[$i]['fulltxt'];
			$page_size = $result[$i]['size'];
			$domain = $result[$i]['domain'];
			if ($page_size!="")
				$page_size = number_format($page_size, 1)."kb";

			$txtlen = strlen($fulltxt);
			if ($txtlen > $desc_length) {
				$places = array();
				foreach($words['hilight'] as $word) {
					$word = latin1_to_html($word);
					$tmp = strtolower($fulltxt);
					$found_in = strpos($tmp, $word);
					$sum = -strlen($word);
					while (!($found_in =='')) {
						$pos = $found_in+strlen($word);
						$sum += $pos;  //FIX!!
						$tmp = substr($tmp, $pos);
						$places[] = $sum;
						$found_in = strpos($tmp, $word);
					}
				}
				sort($places);
				$x = 0;
				$begin = 0;
				$end = 0;
				while(list($id, $place) = each($places)) {
					while (isset($places[$id + $x]) && $places[$id + $x] - $place < $desc_length
						   && $x+$id < count($places)
						   && $place < strlen($fulltxt) - $desc_length) {
						$x++;
						$begin = $id;
						$end = $id + $x;
					}
				}

				if (!isset($places[$begin]))
					$places[$begin] = 0;
				$begin_pos = max(0, $places[$begin] - 30);
				$fulltxt = substr($fulltxt, $begin_pos, $desc_length);

				if ($places[$begin] > 0) {
					$begin_pos = strpos($fulltxt, " ");
				}
				$fulltxt = substr($fulltxt, $begin_pos, $desc_length);
				$fulltxt = substr($fulltxt, 0, strrpos($fulltxt, " "));
				$fulltxt = $fulltxt;
			}

			$weight = number_format($result[$i]['weight']/$maxweight*100, 2);
			if ($title=='') {
				/* for an untitled document, use the filename without the path */
				$pos = strrpos($url, "/");
				if ($pos >= 0)
					$pos++;
				else
					$pos = 0;
				$title = substr($url, $pos);
			}
			$regs = Array();

			if (strlen($title) > 80) {
				$title = substr($title, 0,76)."...";
			}
			foreach($words['hilight'] as $change) {
				$change = latin1_to_html($change);
				$count = 0;
				while (preg_match("/[ .,;\(\)\'\"](".$change.")[ .,;\(\)\'\"]/i", " ".$title." ", $regs) && ++$count < 20) {
					$title = preg_replace("/([ .,;\(\)\'\"])".$regs[1]."([ .,;\(\)\'\"])/i", "$1<b>".$regs[1]."</b>$2", $title);
				}

				$count = 0;
				while (preg_match("/[ .,;\(\)\'\"](".$change.")[ .,\(\)\'\"]/i", " ".$fulltxt." ", $regs) && ++$count < 20) {
					$fulltxt = preg_replace("/([ .,;\(\)\'\"])".$regs[1]."([ .,;\(\)\'\"])/i", "$1<b>".$regs[1]."</b>$2", $fulltxt);
				}
			}

			$num = $from + $i;

			$full_result['qry_results'][$i]['num'] =  $num;
			$full_result['qry_results'][$i]['weight'] =  $weight;
			$full_result['qry_results'][$i]['url'] =  $url;
			$full_result['qry_results'][$i]['title'] =  $title;
			$full_result['qry_results'][$i]['summary'] =  $summary;
			$full_result['qry_results'][$i]['lang'] =  $lang;
			$full_result['qry_results'][$i]['fulltxt'] =  $fulltxt;
			$full_result['qry_results'][$i]['page_size'] =  $page_size;
			$full_result['qry_results'][$i]['domain_name'] =  $domain;
			$i++;
		}
	}


	$pages = ceil($rows / $results_per_page);
	$full_result['pages'] = $pages;
	$prev = $start - 1;
	$full_result['prev'] = $prev;
	$next = $start + 1;
	$full_result['next'] = $next;
	$full_result['start'] = $start;
	$full_result['query'] = $entitiesQuery;

	if ($from <= $to) {

		$firstpage = $start - $links_to_next;
		if ($firstpage < 1) $firstpage = 1;
		$lastpage = $start + $links_to_next;
		if ($lastpage > $pages) $lastpage = $pages;

		for ($x=$firstpage; $x<=$lastpage; $x++)
			$full_result['other_pages'][] = $x;
	}

	return $full_result;

}

?>
