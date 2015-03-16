<?php
/*******************************************
* Sphider Version 1.3.*
* This program is licensed under the GNU GPL.
* By Ando Saabas		  ando(a t)cs.ioc.ee
*
* Thanks to Antoine Bajolet for ideas and
* several code pieces
********************************************/

error_reporting(E_ALL);

	set_time_limit (0);
	$include_dir = "../include";
	include "auth.php";
	require_once ("$include_dir/commonfuncs.php");
	$all = 0;
	extract ($_POST);
	$settings_dir =  "../settings";
	require_once ("$settings_dir/conf.php");

	include "messages.php";
	include "spiderfuncs.php";

	$all_keywords = array();

	$delay_time = 0;


	$command_line = 0;

	if (isset($_SERVER['argv']) && $_SERVER['argc'] >= 2) {
		$command_line = 1;
		$ac = 1; //argument counter
		while ($ac < (count($_SERVER['argv']))) {
			$arg = $_SERVER['argv'][$ac];

			if ($arg  == '-all') {
				$all = 1;
				break;
			} else if ($arg  == '-u') {
				$url = $_SERVER['argv'][$ac+1];
				$ac= $ac+2;
			} else if ($arg  == '-f') {
				$soption = 'full';
				$ac++;
			} else if ($arg == '-d') {
				$soption = 'level';
				$maxlevel =  $_SERVER['argv'][$ac+1];;
				$ac= $ac+2;
			} else if ($arg == '-l') {
				$domaincb = 1;
				$ac++;
			} else if ($arg == '-r') {
				$reindex = 1;
				$ac++;
			} else if ($arg  == '-m') {
				$in =  str_replace("\\n", chr(10), $_SERVER['argv'][$ac+1]);
				$ac= $ac+2;
			} else if ($arg  == '-n') {
				$out =  str_replace("\\n", chr(10), $_SERVER['argv'][$ac+1]);
				$ac= $ac+2;
			} else {
				commandline_help();
				die();
			}

		}
	}


	if (isset($soption) && $soption == 'full') {
		$maxlevel = -1;

	}

	if (!isset($domaincb)) {
		$domaincb = 0;

	}

	if(!isset($reindex)) {
		$reindex=0;
	}

	if(!isset($maxlevel)) {
		$maxlevel=0;
	}


	if ($keep_log) {
		if ($log_format=="html") {
			$log_file =  $log_dir."/".Date("ymdHi").".html";
		} else {
			$log_file =  $log_dir."/".Date("ymdHi").".log";
		}

		if (!$log_handle = fopen($log_file, 'w')) {
			die ("Logging option is set, but cannot open file for logging.");
		}
	}

	if ($all ==  1) {
		index_all();
	} else {

		if ($reindex == 1 && $command_line == 1) {
			$result=$db->query("SELECT url, spider_depth, required, disallowed, can_leave_domain FROM ".TABLE_PREFIX."sites WHERE url=".$db->quote($url));
			echo sql_errorstring(__FILE__,__LINE__);
			if ($row=$result->fetch()) {
				$url = $row[0];
				$maxlevel = $row[1];
				$in= $row[2];
				$out = $row[3];
				$domaincb = $row[4];
				if ($domaincb=='') {
					$domaincb=0;
				}
				if ($maxlevel == -1) {
					$soption = 'full';
				} else {
					$soption = 'level';
				}
			}
			$result->closeCursor();
		}
		if (!isset($in)) {
			$in = "";
		}
		if (!isset($out)) {
			$out = "";
		}

		index_site($url, $reindex, $maxlevel, $soption, $in, $out, $domaincb);

	}

	$tmp_urls  = Array();


	function microtime_float(){
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}


	function index_url($url, $level, $site_id, $md5sum, $domain, $indexdate, $sessid, $can_leave_domain, $reindex) {
		global $min_delay;
		global $command_line;
		global $min_words_per_page;
		global $supdomain, $index_vpaths;
		global $user_agent, $tmp_urls, $delay_time, $domain_arr;
		global $db;
		$deletable = 0;

		$url_status = url_status($url);
		$thislevel = $level - 1;

		if (strstr($url_status['state'], "Relocation")) {
			$url = preg_replace("/ /", "", url_purify($url_status['path'], $url, $can_leave_domain));

			if ($url <> '') {
				$result = $db->query("SELECT link FROM ".TABLE_PREFIX."temp WHERE link=".$db->quote($url)." AND id=".$db->quote($sessid));
				echo sql_errorstring(__FILE__,__LINE__);
				if ($result->fetch()) {
					$result->closeCursor();
					$db->exec("INSERT INTO ".TABLE_PREFIX."temp (link, level, id) VALUES (".$db->quote($url).", ".$db->quote($level).", ".$db->quote($sessid).")");
					echo sql_errorstring(__FILE__,__LINE__);
				}
			}

			$url_status['state'] == "redirected";
		}

		if (!$index_vpaths && $url_status['state'] == 'ok') {
			$url_parts = parse_url($url);
			$base = basename($url_parts['path']);
			if (strstr($base, '.') == false) {
				$url_status['state'] = "directory listing or default redirect";
			}
		}

		ini_set("user_agent", $user_agent);
		if ($url_status['state'] == 'ok') {
			$OKtoIndex = 1;
			$file_read_error = 0;

			if (time() - $delay_time < $min_delay) {
				sleep ($min_delay- (time() - $delay_time));
			}
			$delay_time = time();
			if (!fst_lt_snd(phpversion(), "4.3.0")) {
				$file = file_get_contents($url);
				if ($file === FALSE) {
					$file_read_error = 1;
				}
			} else {
				$fl = @fopen($url, "r");
				if ($fl) {
					while ($buffer = @fgets($fl, 4096)) {
						$file .= $buffer;
					}
				} else {
					$file_read_error = 1;
				}

				fclose ($fl);
			}
			if ($file_read_error) {
				$contents = getFileContents($url);
				$file = $contents['file'];
			}

			$pageSize = number_format(strlen($file)/1024, 2, ".", "");
			printPageSizeReport($pageSize);

			if ($url_status['content'] != 'text')
				$file = extract_text($file, $url_status['content']);

			printStandardReport('starting', $command_line);

			$newmd5sum = md5($file);

			if ($reindex == 0) {
				if ($md5sum == $newmd5sum) {
					printStandardReport('md5notChanged',$command_line);
					$OKtoIndex = 0;
				} else if (isDuplicateMD5($newmd5sum)) {
					$OKtoIndex = 0;
					printStandardReport('duplicate',$command_line);
				}
			}

			if (($md5sum != $newmd5sum || $reindex == 1) && $OKtoIndex == 1) {
				$urlparts = parse_url($url);
				$newdomain = $urlparts['host'];
				$type = 0;

				// remove link to css file
				//get all links from file
				$data = clean_file($file, $url, $url_status['content']);

				if ($data['noindex'] == 1) {
					$OKtoIndex = 0;
					$deletable = 1;
					printStandardReport('metaNoindex',$command_line);
				}


				$wordarray = unique_array(explode(" ", $data['content']));

				if ($data['nofollow'] != 1) {
					$links = get_links($file, $url, $can_leave_domain, $data['base']);
					$links = distinct_array($links);
					$all_links = count($links);
					$numoflinks = 0;
					//if there are any, add to the temp table, but only if there isnt such url already
					if (is_array($links)) {
						reset ($links);

						while ($thislink = each($links)) {
							if (!isset($tmp_urls[$thislink[1]]) || $tmp_urls[$thislink[1]] != 1) {
								$tmp_urls[$thislink[1]] = 1;
								$numoflinks++;
								$db->exec("INSERT INTO ".TABLE_PREFIX."temp (link, level, id) VALUES (".$db->quote($thislink[1]).", ".$db->quote($level).", ".$db->quote($sessid).")");
								echo sql_errorstring(__FILE__,__LINE__);
							}
						}
					}
				} else {
					printStandardReport('noFollow',$command_line);
				}

				if ($OKtoIndex == 1) {

					$title = $data['title'];
					$host = $data['host'];
					$path = $data['path'];
					$fulltxt = str_replace("\\'","&quot;",$data['fulltext']);

					$desc = substr($data['description'],0,254);
					$language = substr($data['language'],0,2);
					$url_parts = parse_url($url);
					$domain_for_db = $url_parts['host'];

					if (isset($domain_arr[$domain_for_db])) {
						$dom_id = $domain_arr[$domain_for_db];
					} else {
						$db->exec("INSERT INTO ".TABLE_PREFIX."domains (domain) VALUES (".$db->quote($domain_for_db).")");
						$dom_id = $db->lastInsertId();
						$domain_arr[$domain_for_db] = $dom_id;
					}

					$wordarray = calc_weights ($wordarray, $title, $host, $path, $data['keywords']);
                                        $tstamp = "'".date("Y-m-d")."'";

					//if there are words to index, add the link to the database, get its id, and add the word + their relation
					if (is_array($wordarray) && count($wordarray) > $min_words_per_page) {
						$site_id = $db->quote($site_id);
						$url = $db->quote($url);
						$title = $db->quote($title);
						$desc = $db->quote($desc);
						$language = $db->quote($language);
						$fulltxt = $db->quote($fulltxt);
						$pageSize = $db->quote($pageSize);
						$Qmd5sum = $db->quote($newmd5sum);
						if ($md5sum == '') {
							$db->exec("INSERT INTO ".TABLE_PREFIX."links (site_id, url, title, description, language, fulltxt, indexdate, size, md5sum, level) VALUES ($site_id, $url, $title, $desc, $language, $fulltxt, $tstamp, $pageSize, $Qmd5sum, $thislevel)");
							$error = sql_errorstring(__FILE__,__LINE__);
							if ($error) {
							  echo $error;
							  printStandardReport('skipped', $command_line);
							} else {
							  $result = $db->query("SELECT link_id FROM ".TABLE_PREFIX."links WHERE url=$url");
							  echo sql_errorstring(__FILE__,__LINE__);
							  $row = $result->fetch();
							  $link_id = $row[0];
							  $result->closeCursor();
							  save_keywords($wordarray, $link_id, $dom_id);
							  printStandardReport('indexed', $command_line);
							}
						} else if (($md5sum <> '') && ($md5sum <> $newmd5sum)) { //if page has changed, start updating
							$result = $db->query("SELECT link_id FROM ".TABLE_PREFIX."links WHERE url=$url");
							echo sql_errorstring(__FILE__,__LINE__);
							$row = $result->fetch();
							$link_id = $row[0];
							$result->closeCursor();
							for ($i=0;$i<=15; $i++) {
								$char = dechex($i);
								$db->exec("DELETE FROM ".TABLE_PREFIX."link_keyword$char WHERE link_id=$link_id");
								echo sql_errorstring(__FILE__,__LINE__);
							}
							save_keywords($wordarray, $link_id, $dom_id);
							$db->exec("UPDATE ".TABLE_PREFIX."links SET title=$title, description=$desc, language=$language, fulltxt=$fulltxt, indexdate=$tstamp, size=$pageSize, md5sum=$Qmd5sum, level=$thislevel WHERE link_id=$link_id");
							echo sql_errorstring(__FILE__,__LINE__);
							printStandardReport('re-indexed', $command_line);
						}
					} else {
						printStandardReport('minWords', $command_line);
					}
				}
			}
		} else {
			$deletable = 1;
			printUrlStatus($url_status['state'], $command_line);

		}
		if ($reindex ==1 && $deletable == 1) {
			check_for_removal($url);
		} else if ($reindex == 1) {
			//???
		}
		if (!isset($all_links)) {
			$all_links = 0;
		}
		if (!isset($numoflinks)) {
			$numoflinks = 0;
		}
		printLinksReport($numoflinks, $all_links, $command_line);
	}


	function index_site($url, $reindex, $maxlevel, $soption, $url_inc, $url_not_inc, $can_leave_domain) {
		global $command_line, $mainurl,  $tmp_urls, $domain_arr, $all_keywords;
		global $db;

		if (!isset($all_keywords) || !is_array($all_keywords) || count($all_keywords) <= 0) {
			$result = $db->query("SELECT keyword_ID, keyword FROM ".TABLE_PREFIX."keywords");
			echo sql_errorstring(__FILE__,__LINE__);
			while($row=$result->fetch())
				$all_keywords[$row[1]] = $row[0];
		}
		$compurl = parse_url($url);
		if ($compurl['path'] == '')
			$url = $url . "/";

		$t = microtime();
		$a =  getenv("REMOTE_ADDR");
		$sessid = md5($t.$a);


		$urlparts = parse_url($url);

		$domain = $urlparts['host'];
		if (isset($urlparts['port'])) {
			$port = (int)$urlparts['port'];
		}else {
			$port = 80;
		}


		$result = $db->query("select site_id from ".TABLE_PREFIX."sites where url='$url'");
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		$site_id = $row[0];
		$result->closeCursor();
                $tstamp = "'".date("Y-m-d")."'";

		if ($site_id != "" && $reindex == 1) {
			$db->exec("insert into ".TABLE_PREFIX."temp (link, level, id) values ('$url', 0, '$sessid')");
			echo sql_errorstring(__FILE__,__LINE__);
			$result = $db->query("select url, level from ".TABLE_PREFIX."links where site_id = $site_id");
			while ($row = $result->fetch()) {
				$site_link = $row['url'];
				$link_level = $row['level'];
				if ($site_link != $url) {
					$db->exec("insert into ".TABLE_PREFIX."temp (link, level, id) values ('$site_link', $link_level, '$sessid')");
				}
			}

			$qry = "update ".TABLE_PREFIX."sites set indexdate=$tstamp, spider_depth=$maxlevel, required='$url_inc'," .
					"disallowed='$url_not_inc', can_leave_domain=$can_leave_domain where site_id=$site_id";
			$db->exec($qry);
			echo sql_errorstring(__FILE__,__LINE__);
		} else if ($site_id == "") {
			$db->exec("insert into ".TABLE_PREFIX."sites (url, indexdate, spider_depth, required, disallowed, can_leave_domain) " .
					"values ('$url', $tstamp, $maxlevel, '$url_inc', '$url_not_inc', $can_leave_domain)");
			echo sql_errorstring(__FILE__,__LINE__);
			$result = $db->query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
			$row = $result->fetch();
			$site_id = $row[0];
			$result->closeCursor();
		} else {
			$db->exec("update ".TABLE_PREFIX."sites set indexdate=$tstamp, spider_depth=$maxlevel, required='$url_inc'," .
					"disallowed='$url_not_inc', can_leave_domain=$can_leave_domain where site_id=$site_id");
			echo sql_errorstring(__FILE__,__LINE__);
		}


		$result = $db->query("select site_id, temp_id, level, count, num from ".TABLE_PREFIX."pending where site_id='$site_id'");
		echo sql_errorstring(__FILE__,__LINE__);
		$row = $result->fetch();
		$pending = $row[0];
		$result->closeCursor();
		$level = 0;
		$domain_arr = get_domains();
		if ($pending == '') {
			$db->exec("insert into ".TABLE_PREFIX."temp (link, level, id) values ('$url', 0, '$sessid')");
			echo sql_errorstring(__FILE__,__LINE__);
		} else if ($pending != '') {
			printStandardReport('continueSuspended',$command_line);
			$result = $db->query("select temp_id, level, count from ".TABLE_PREFIX."pending where site_id='$site_id'");
			echo sql_errorstring(__FILE__,__LINE__);
			$row = $result->fetch();
			$sessid = $row[1];
			$level = $row[2];
			$pend_count = isset($row[3]) ? $row[3] + 1 : 1;
			$num = isset($row[4]) ? $row[4] : 0;
			$result->closeCursor();
			$pending = 1;
			$tmp_urls = get_temp_urls($sessid);
		}

		if ($reindex != 1) {
			$db->exec("insert into ".TABLE_PREFIX."pending (site_id, temp_id, level, count) values ('$site_id', '$sessid', '0', '0')");
			echo sql_errorstring(__FILE__,__LINE__);
		}


		$time = time();

		$omit = check_robot_txt($url);

		printHeader($omit, $url, $command_line);

		$mainurl = $url;
		$num = 0;

		while (($level <= $maxlevel && $soption == 'level') || ($soption == 'full')) {
			if ($pending == 1) {
				$count = $pend_count;
				$pending = 0;
			} else
				$count = 0;

			$links = array();

			$result = $db->query("select distinct link from ".TABLE_PREFIX."temp where level=$level AND id='$sessid' order by link");
			echo sql_errorstring(__FILE__,__LINE__);
			$row = $result->fetch();
			if (! $row) {
				break;
			}

			$i = 0;
			$links[] = $row['link'];
			while ($row = $result->fetch()) {
				$links[] = $row['link'];
			}

			reset ($links);


			while ($count < count($links)) {
				$num++;
				$thislink = $links[$count];
				$urlparts = parse_url($thislink);
				reset ($omit);
				$forbidden = 0;
				foreach ($omit as $omiturl) {
					$omiturl = trim($omiturl);

					$omiturl_parts = parse_url($omiturl);
					if (!isset($omiturl_parts['scheme']) || $omiturl_parts['scheme'] == '') {
						$check_omit = $urlparts['host'] . $omiturl;
					} else {
						$check_omit = $omiturl;
					}

					if (strpos($thislink, $check_omit)) {
						printRobotsReport($num, $thislink, $command_line);
						check_for_removal($thislink);
						$forbidden = 1;
						break;
					}
				}

				if (!check_include($thislink, $url_inc, $url_not_inc )) {
					printUrlStringReport($num, $thislink, $command_line);
					check_for_removal($thislink);
					$forbidden = 1;
				}

				if ($forbidden == 0) {
					printRetrieving($num, $thislink, $command_line);
					$query = "select md5sum, indexdate from ".TABLE_PREFIX."links where url='$thislink'";
					$result = $db->query($query);
					echo sql_errorstring(__FILE__,__LINE__);
					$row = $result->fetch();
					$result->closeCursor();
					if (! $row) {
						index_url($thislink, $level+1, $site_id, '',  $domain, '', $sessid, $can_leave_domain, $reindex);
						$db->exec("update ".TABLE_PREFIX."pending set level = $level, count=$count, num=$num where site_id=$site_id");
						echo sql_errorstring(__FILE__,__LINE__);
					}else if ($reindex == 1) {
						$md5sum = $row['md5sum'];
						$indexdate = $row['indexdate'];
						index_url($thislink, $level+1, $site_id, $md5sum,  $domain, $indexdate, $sessid, $can_leave_domain, $reindex);
						$db->exec("update ".TABLE_PREFIX."pending set level = $level, count=$count, num=$num where site_id=$site_id");
						echo sql_errorstring(__FILE__,__LINE__);
					}else {
						printStandardReport('inDatabase',$command_line);
					}

				}
				$count++;
			}
			$level++;
		}

		$db->exec("delete from ".TABLE_PREFIX."temp where id = '$sessid'");
		echo sql_errorstring(__FILE__,__LINE__);
		$db->exec("delete from ".TABLE_PREFIX."pending where site_id = '$site_id'");
		echo sql_errorstring(__FILE__,__LINE__);
		printStandardReport('completed',$command_line);


	}

	function index_all() {
		global $db;
		$result=$db->query("select url, spider_depth, required, disallowed, can_leave_domain from ".TABLE_PREFIX."sites");
		echo sql_errorstring(__FILE__,__LINE__);
		while ($row=$result->fetch()) {
			$url = $row[0];
			$depth = $row[1];
			$include = $row[2];
			$not_include = $row[3];
			$can_leave_domain = $row[4];
			if ($can_leave_domain=='') {
				$can_leave_domain=0;
			}
			if ($depth == -1) {
				$soption = 'full';
			} else {
				$soption = 'level';
			}
			index_site($url, 1, $depth, $soption, $include, $not_include, $can_leave_domain);
		}
	}

	function get_temp_urls ($sessid) {
		global $db;
		$result = $db->query("select link from ".TABLE_PREFIX."temp where id='$sessid'");
		echo sql_errorstring(__FILE__,__LINE__);
		$tmp_urls = Array();
		while ($row=$result->fetch()) {
			$tmp_urls[$row[0]] = 1;
		}
		return $tmp_urls;

	}

	function get_domains () {
		global $db;
		$result = $db->query("select domain_id, domain from ".TABLE_PREFIX."domains");
		echo sql_errorstring(__FILE__,__LINE__);
		$domains = Array();
		while ($row=$result->fetch()) {
			$domains[$row[1]] = $row[0];
		}
		return $domains;

	}

	function commandline_help() {
		print "Usage: php spider.php <options>\n\n";
		print "Options:\n";
		print " -all\t\t Reindex everything in the database\n";
		print " -u <url>\t Set url to index\n";
		print " -f\t\t Set indexing depth to full (unlimited depth)\n";
		print " -d <num>\t Set indexing depth to <num>\n";
		print " -l\t\t Allow spider to leave the initial domain\n";
		print " -r\t\t Set spider to reindex a site\n";
		print " -m <string>\t Set the string(s) that an url must include (use \\n as a delimiter between multiple strings)\n";
		print " -n <string>\t Set the string(s) that an url must not include (use \\n as a delimiter between multiple strings)\n";
	}

	printStandardReport('quit',$command_line);
	if ($email_log) {
		$indexed = ($all==1) ? 'ALL' : $url;
		$log_report = "";
		if ($log_handle) {
			$log_report = "Log saved into $log_file";
		}
		mail($admin_email, "Sphider indexing report", "Sphider has finished indexing $indexed at ".date("y-m-d H:i:s").". ".$log_report);
	}
	if ( $log_handle) {
		fclose($log_handle);
	}

?>
