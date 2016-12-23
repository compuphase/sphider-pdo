<?php

include "auth.php";
if (!CONFIGSET)
    exit("Function disabled");

if (!isset($_index_numbers))
    $_index_numbers=0;

if (!isset($_index_xls))
    $_index_xls=0;

if (!isset($_index_ppt))
    $_index_ppt=0;

if (!isset($_index_pdf))
    $_index_pdf=0;

if (!isset($_index_doc))
    $_index_doc=0;

if (!isset($_min_delay))
    $_min_delay=0;

if (!isset($_index_host))
    $_index_host=0;

if (!isset($_keep_log))
    $_keep_log=0;

if (!isset($_show_meta_description))
    $_show_meta_description=0;

if (!isset($_show_categories))
    $_show_categories=0;

if (!isset($_show_query_scores))
    $_show_query_scores=0;

if (!isset($_email_log))
    $_email_log=0;

if (!isset($_print_results))
    $_print_results=0;

if (!isset($_index_meta_keywords))
    $_index_meta_keywords=0;

if (!isset($_index_host))
    $_index_host=0;

if (!isset($_index_vpaths))
    $_index_vpaths=1;

if (!isset($_advanced_search))
    $_advanced_search=0;

if (!isset($_merge_site_results))
    $_merge_site_results=0;

if (!isset($_did_you_mean_enabled))
    $_did_you_mean_enabled=0;

if (!isset($_did_you_mean_always))
    $_did_you_mean_always=1;

if (!isset($_stem_words))
    $_stem_words=0;

if (!isset($_strip_sessids))
    $_strip_sessids=0;

if (!isset($_suggest_enabled))
    $_suggest_enabled=0;

if (!isset($_suggest_history))
    $_suggest_history=0;

if (!isset($_suggest_phrases))
    $_suggest_phrases=0;

if (!isset($_suggest_keywords))
    $_suggest_keywords=0;

if (!isset($_suggest_rows))
    $_suggest_rows=0;


if (isset($Submit)) {
    if (!is_writable("../settings/conf.php")) {
        print "Configuration file is not writable, chmod 666 conf.php under *nix systems";
    } else {
        $fhandle=fopen("../settings/conf.php","wb");
        fwrite($fhandle,"<?php \n");
        fwrite($fhandle,"/***********************\n Sphider configuration file\n***********************/");
        fwrite($fhandle,"\n\n\n/*********************** \nGeneral settings \n***********************/");
        fwrite($fhandle, "\n\n// Sphider version \n");
        fwrite($fhandle,"$"."version_nr = '".$_version_nr. "';");
        fwrite($fhandle, "\n\n// Language of the search page (can be overridden)\n");
        fwrite($fhandle,"$"."language = '".$_language. "';");
        fwrite($fhandle, "\n\n// Template name/directory in templates dir\n");
        fwrite($fhandle,"$"."template = '".$_template. "';");
        fwrite($fhandle, "\n\n//Administrators email address (logs can be sent there)   \n");
        fwrite($fhandle,"$"."admin_email = '".$_admin_email. "';");
        fwrite($fhandle, "\n\n// Print spidering results to standard out\n");
        fwrite($fhandle,"$"."print_results = ".$_print_results. ";");
        fwrite($fhandle, "\n\n// Temporary directory, this should be readable and writable\n");
        fwrite($fhandle,"$"."tmp_dir = '".$_tmp_dir. "';");

        fwrite($fhandle,"\n\n\n/*********************** \nLogging settings \n***********************/");
        fwrite($fhandle, "\n\n// Should log files be kept\n");
        fwrite($fhandle,"$"."keep_log = ".$_keep_log. ";");
        fwrite($fhandle, "\n\n//Log directory, this should be readable and writable\n");
        fwrite($fhandle,"$"."log_dir = '".$_log_dir. "';");
        fwrite($fhandle, "\n\n// Log format\n");
        fwrite($fhandle,"$"."log_format = '".$_log_format. "';");
        fwrite($fhandle, "\n\n//  Send log file to email \n");
        fwrite($fhandle,"$"."email_log = ".$_email_log. ";");

        fwrite($fhandle,"\n\n\n/*********************** \nSpider settings \n***********************/\n\n");
        fwrite($fhandle, "// Minimum words that a must have to be spidered\n");
        fwrite($fhandle,"$"."min_words_per_page = ".$_min_words_per_page. ";\n\n");
        fwrite($fhandle, "// Words shorter than this will be ignored (never considered a keyword)\n");
        fwrite($fhandle,"$"."min_word_length = ".$_min_word_length. ";\n\n");
        fwrite($fhandle, "// Keyword weight depending on the number of times it appears in a page is capped\n" .
                         "// at this value\n");
        fwrite($fhandle,"$"."word_upper_bound = ".$_word_upper_bound. ";\n\n");
        fwrite($fhandle, "// Whether to consider numbers to be keywords\n");
        fwrite($fhandle,"$"."index_numbers = ".$_index_numbers. ";\n\n");
        fwrite($fhandle,"// If this value is set to 1, a link to a directory is spidered too. If this value\n" .
                        "// is set to 0, only files are spidered. If your site has links to directories as\n" .
                        "// well as files, you may want to set this value to 0, to avoid MD5-hash collisions\n" .
                        "// when \"www.mydomain.com/\" and \"www.mydomain.com/index.html\" are both spidered.\n");
        fwrite($fhandle,"$"."index_vpaths = ".$_index_vpaths.";\n\n");
        fwrite($fhandle,"// if this value is set to 1, words in domain name and url path are also taken as keywords,\n" .
                        "// so that for example the index of www.php.net returns a positive answer to query\n" .
                        "// 'php' even if the word is not included in the page itself.\n");
        fwrite($fhandle,"$"."index_host = ".$_index_host.";\n\n");
        fwrite($fhandle, "// Wether to include keywords in a meta tag\n");
        fwrite($fhandle,"$"."index_meta_keywords = ".$_index_meta_keywords. ";\n\n");
        fwrite($fhandle, "// Spider pdf files\n");
        fwrite($fhandle,"$"."index_pdf = ".$_index_pdf. ";\n\n");
        fwrite($fhandle, "// Spider doc files\n");
        fwrite($fhandle,"$"."index_doc = ".$_index_doc. ";\n\n");
        fwrite($fhandle, "// Spider xls files\n");
        fwrite($fhandle,"$"."index_xls = ".$_index_xls. ";\n\n");
        fwrite($fhandle, "// Spider ppt files\n");
        fwrite($fhandle,"$"."index_ppt = ".$_index_ppt. ";\n\n");
        fwrite($fhandle, "// Executable path to pdf converter\n");
        fwrite($fhandle,"$"."pdftotext_path = '".$_pdftotext_path   . "';\n\n");
        fwrite($fhandle, "// Executable path to doc converter\n");
        fwrite($fhandle,"$"."catdoc_path = '".$_catdoc_path. "';\n\n");
        fwrite($fhandle, "// Executable path to xls converter\n");
        fwrite($fhandle,"$"."xls2csv_path = '".$_xls2csv_path   . "';\n\n");
        fwrite($fhandle, "// Executable path to ppt converter\n");
        fwrite($fhandle,"$"."catppt_path = '".$_catppt_path. "';\n\n");
        fwrite($fhandle, "// User agent string \n");
        fwrite($fhandle,"$"."user_agent = '".$_user_agent. "';\n\n");
        fwrite($fhandle, "// Minimal delay between page downloads\n");
        fwrite($fhandle,"$"."min_delay = ".$_min_delay. ";\n\n");
        fwrite($fhandle, "// Use word stemming (e.g. find sites containing runs and running when searching\n" .
                         "// for run)\n");
        fwrite($fhandle,"$"."stem_words = ".$_stem_words. ";\n\n");
        fwrite($fhandle, "// Strip session ids (PHPSESSID, JSESSIONID, ASPSESSIONID, sid)\n");
        fwrite($fhandle,"$"."strip_sessids = ".$_strip_sessids. ";\n\n");

        fwrite($fhandle,"\n/*********************** \nSearch settings \n***********************/");
        fwrite($fhandle, "\n\n// default for number of results per page\n");
        fwrite($fhandle,"$"."results_per_page = ".$_results_per_page. ";");
        fwrite($fhandle, "\n\n// Number of columns for categories. If you increase this, you might also want to increase the category table with in the css file\n");
        fwrite($fhandle,"$"."cat_columns = ".$_cat_columns. ";");
        fwrite($fhandle, "\n\n// Can speed up searches on large database (should be 0)\n");
        fwrite($fhandle,"$"."bound_search_result = ".$_bound_search_result. ";");
        fwrite($fhandle,"\n\n// The length of the description string queried when displaying search results.\n// If set to 0 (default), makes a query for the whole page text,\n// otherwise queries this many bytes. Can significantly speed up searching on very slow machines \n");
        fwrite($fhandle,"$"."length_of_link_desc = ".$_length_of_link_desc. ";");
        fwrite($fhandle, "\n\n// Number of links shown to next pages\n");
        fwrite($fhandle,"$"."links_to_next = ".$_links_to_next. ";");
        fwrite($fhandle, "\n\n// Show meta description in results page if it exists, otherwise show an extract from the page text.\n");
        fwrite($fhandle,"$"."show_meta_description = ".$_show_meta_description. ";");
        fwrite($fhandle, "\n\n// Advanced query form, shows and/or buttons\n");
        fwrite($fhandle,"$"."advanced_search = ".$_advanced_search. ";");
        fwrite($fhandle, "\n\n// Query scores are not shown if set to 0\n");
        fwrite($fhandle,"$"."show_query_scores = ".$_show_query_scores. ";    ");
        fwrite($fhandle, "\n\n");
        fwrite($fhandle, "\n\n // Display category list\n");
        fwrite($fhandle,"$"."show_categories = ".$_show_categories. ";");
        fwrite($fhandle, "\n\n// Length of page description given in results page\n");
        fwrite($fhandle,"$"."desc_length = ".$_desc_length. ";");
        fwrite($fhandle, "\n\n// Show only the 2 most relevant links from each site (a la google)\n");
        fwrite($fhandle,"$"."merge_site_results = ".$_merge_site_results. ";");
        fwrite($fhandle, "\n\n// Enable spelling suggestions (Did you mean...)\n");
        fwrite($fhandle,"$"."did_you_mean_enabled = ".$_did_you_mean_enabled. ";");
        fwrite($fhandle, "\n\n// Always search for alternative spellings, not just when there are no results\n");
        fwrite($fhandle,"$"."did_you_mean_always = ".$_did_you_mean_always. ";");
        fwrite($fhandle, "\n\n// Enable Sphider Suggest \n");
        fwrite($fhandle,"$"."suggest_enabled = ".$_suggest_enabled. ";");
        fwrite($fhandle, "\n\n// Search for suggestions in query log \n");
        fwrite($fhandle,"$"."suggest_history = ".$_suggest_history. ";");
        fwrite($fhandle, "\n\n// Search for suggestions in keywords \n");
        fwrite($fhandle,"$"."suggest_keywords = ".$_suggest_keywords. ";");
        fwrite($fhandle, "\n\n// Search for suggestions in phrases \n");
        fwrite($fhandle,"$"."suggest_phrases = ".$_suggest_phrases. ";");
        fwrite($fhandle, "\n\n// Limit number of suggestions \n");
        fwrite($fhandle,"$"."suggest_rows = ".$_suggest_rows. ";");

        fwrite($fhandle,"\n\n\n/*********************** \nWeights\n***********************/");
        fwrite($fhandle, "\n\n// Relative weight of a word in the title of a webpage\n");
        fwrite($fhandle,"$"."title_weight = ".$_title_weight. ";");
        fwrite($fhandle, "\n\n// Relative weight of a word in the domain name\n");
        fwrite($fhandle,"$"."domain_weight = ".$_domain_weight. ";");
        fwrite($fhandle, "\n\n// Relative weight of a word in the path name\n");
        fwrite($fhandle,"$"."path_weight = ".$_path_weight. ";");
        fwrite($fhandle, "\n\n// Relative weight of a word in meta_keywords\n");
        fwrite($fhandle,"$"."meta_weight = ".$_meta_weight. ";\n");

        fwrite($fhandle,"?>");
        fclose($fhandle);
    }
}
include "../settings/conf.php";
?>
<div id='submenu'>&nbsp;</div>
<div id="settings">

<form name="form1" method="post" action="admin.php">
<input type="hidden" name="f" value="settings">
<input type="hidden" name="Submit" value="1">
<table>
<tr>
  <td colspan="4"><div class="tableSubHeading">General settings</div></td>
</tr>

<tr>
  <td class="left1">
    <input name="_version_nr" value="<?php print $version_nr;?>" type="hidden">
    <b></b><?php print $version_nr;?></b>
  </td>
  <td colspan="3">
    Sphider version
  </td>
</tr>

<tr>
  <td class="left1">
    <select name="_language">
      <option value="ar"  <?php  if ($language == "ar") echo "selected";?>>Arabic</option>
      <option value="bg"  <?php  if ($language == "bg") echo "selected";?>>Bulgarian</option>
      <option value="hr"  <?php  if ($language == "hr") echo "selected";?>>Croatian</option>
      <option value="cns" <?php  if ($language == "cns") echo "selected";?>>Simple Chinese</option>
      <option value="cnt" <?php  if ($language == "cnt") echo "selected";?>>Traditional Chinese</option>
      <option value="cz"  <?php  if ($language == "cz") echo "selected";?>>Czech</option>
      <option value="nl"  <?php  if ($language == "nl") echo "selected";?>>Dutch</option>
      <option value="en"  <?php  if ($language == "en") echo "selected";?>>English</option>
      <option value="ee"  <?php  if ($language == "ee") echo "selected";?>>Estonian</option>
      <option value="fi"  <?php  if ($language == "fi") echo "selected";?>>Finnish</option>
      <option value="fr"  <?php  if ($language == "fr") echo "selected";?>>French</option>
      <option value="fy"  <?php  if ($language == "fy") echo "selected";?>>Frysk</option>
      <option value="de"  <?php  if ($language == "de") echo "selected";?>>German</option>
      <option value="hu"  <?php  if ($language == "hu") echo "selected";?>>Hungarian</option>
      <option value="it"  <?php  if ($language == "it") echo "selected";?>>Italian</option>
      <option value="lv"  <?php  if ($language == "lv") echo "selected";?>>Latvian</option>
      <option value="pl"  <?php  if ($language == "pl") echo "selected";?>>Polish</option>
      <option value="pt"  <?php  if ($language == "pt") echo "selected";?>>Portuguese</option>
      <option value="ro"  <?php  if ($language == "ro") echo "selected";?>>Romanian</option>
      <option value="ru"  <?php  if ($language == "ru") echo "selected";?>>Russian</option>
      <option value="sr"  <?php  if ($language == "sr") echo "selected";?>>Serbian</option>
      <option value="sk"  <?php  if ($language == "sk") echo "selected";?>>Slovak</option>
      <option value="si"  <?php  if ($language == "si") echo "selected";?>>Slovenian</option>
      <option value="es"  <?php  if ($language == "es") echo "selected";?>>Spanish</option>
      <option value="se"  <?php  if ($language == "se") echo "selected";?>>Swedish</option>
      <option value="tr"  <?php  if ($language == "tr") echo "selected";?>>Turkish</option>
    </select>
  </td>
  <td colspan="3">
    <acronym title="This default language setting may be overruled by tags in the individual pages.">
    Language (applies to search page)
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <select name="_template">
<?php
      $directories = get_dir_contents($template_dir);
      if (count($directories)>0) {
        for ($i=0; $i<count($directories); $i++) {
          $dir=$directories[$i];
?>
          <option value="<?php print $dir;?>" <?php  if ($template == $dir) echo "selected";?>><?php print $dir;?></option>
<?php
        }
      }
?>
    </select>
  </td>
  <td colspan="3">
    Search template
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_admin_email" value="<?php print $admin_email;?>" type="text" id="admin_email" size="20">
  </td>
  <td colspan="3">
    Administrator e-mail address
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_print_results" type="checkbox" id="print_results" value="1" <?php if ($print_results==1) echo "checked";?> >
  </td>
  <td colspan="3">
    Print spidering results to standard out
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_tmp_dir" type="text"  value="<?php print $tmp_dir;?>" id="tmp_dir" size="20">
  </td>
  <td colspan="3">
    Temporary directory (absolute or relative to admin directory)
  </td>
</tr>

<tr>
  <td colspan="4"><div class="tableSubHeading">Logging settings</div></td>
</tr>

<tr>
  <td class="left1">
    <input name="_keep_log" type="checkbox" id="keep_log" value="1" <?php if ($keep_log==1) echo "checked";?> >
  </td>
  <td>
    Log spidering results
  </td>
  <td class="left1">
    <input name="_log_dir" type="text"  value="<?php print $log_dir;?>" id="log_dir" size="20">
  </td>
  <td>
    Log directory (absolute or relative to admin directory)
  </td>
</tr>

<tr>
  <td class="left1">
    <select name="_log_format">
      <option value="text" <?php  if ($log_format == "text") echo "selected";?>>Text</option>
      <option value="html" <?php  if ($log_format == "html") echo "selected";?>>Html</option>
    </select>
  </td>
  <td colspan="3">
    Log file format
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_email_log" type="checkbox" id="email_log" value="1" <?php if ($email_log==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Send spidering log to e-mail
  </td>
</tr>

<tr>
  <td colspan="4">
    <div class="tableSubHeading">Spider settings</div>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_min_words_per_page" value="<?php print $min_words_per_page;?>" type="text" id="min_words_per_page" size="5" maxlength="5">
  </td>
  <td colspan="3">
    <acronym title="A page with fewer words is skipped.">
    Minimum number of words on a page to be a candidate for spidering.
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_min_word_length" type="text" value="<?php print $min_word_length;?>" id="min_word_length" size="5" maxlength="2">
  </td>
  <td colspan="3">
    <acronym title="Words whorter than this are ignored. Note that you can also define words to ignore in the common.txt file.">
    Minimum word length in order to be added as a keyword
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_word_upper_bound" type="text" value="<?php print $word_upper_bound;?>" id="word_upper_bound" size="5" maxlength="3">
  </td>
  <td colspan="3">
    <acronym title="If a words appears on a page more often than this number, its count will be set to this number.">
    Keyword weight depending on the number of times it appears in a page is capped at this value
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_numbers" type="checkbox" value="1" id="index_numbers" <?php if ($index_numbers==1) echo "checked";?>>
  </td>
  <td colspan="3">
    <acronym title="If not set, all numbers are skipped (and never added as a keyword)">
    Consider numbers as keywords
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_host" type="checkbox" value="1" id="index_host" <?php if ($index_host==1) echo "checked";?>>
  </td>
  <td colspan="3">
    <acronym title='if set, "example" and "index" in "http://www.example.com/index.html" would be keywords for that page, even if these words do not appear in the page contents.'>
    Consider words the URL as keywords
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_meta_keywords" type="checkbox" value="1" id="index_meta_keywords" <?php if ($index_meta_keywords==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Include the keywords in the "meta" tag
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_vpaths" type="checkbox" value="1" id="index_vpaths" <?php if ($index_vpaths==1) echo "checked";?>>
  </td>
  <td colspan="3">
    <acronym title='if set, an URL without a filename, like "http://www.example.com/", is spidered (you may want to clear this if your site has links to URLs with default redirects, and you get MD5 collisions because both "http://www.example.com/" and "http://www.example.com/index.html" are indexed).'>
    Spider URLs that do not end with a filename
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_pdf" type="checkbox"  value="1" id="index_pdf" <?php if ($index_pdf==1) echo "checked";?> >
  </td>
  <td>
    Spider PDF files
  </td>
  <td class="left1">
    <input name="_pdftotext_path" type="text"  value="<?php print $pdftotext_path;?>" id="pdftotext_path">
  </td>
  <td>
    Full executable path to PDF converter
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_doc" type="checkbox"  value="1" id="index_doc" <?php if ($index_doc==1) echo "checked";?> >
  </td>
  <td>
    Spider DOC files
  </td>
  <td class="left1">
    <input name="_catdoc_path" type="text"  value="<?php print $catdoc_path;?>" id="catdoc_path">
  </td>
  <td>
    Full executable path to catdoc converter
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_xls" type="checkbox"  value="1" id="index_xls" <?php if ($index_xls==1) echo "checked";?> >
  </td>
  <td>
    Spider XLS files
  </td>
  <td class="left1">
    <input name="_xls2csv_path" type="text"  value="<?php print $xls2csv_path;?>" id="xls2csv_path">
  </td>
  <td>
    Full executable path to XLS converter
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_index_ppt" type="checkbox"  value="1" id="index_ppt" <?php  if ($index_ppt==1) echo "checked";?> >
  </td>
  <td>
    Spider PPT files
  </td>
  <td class="left1">
    <input name="_catppt_path" type="text"  value="<?php print $catppt_path;?>" id="catppt_path">
  </td>
  <td>
    Full executable path to PPT converter
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_user_agent" value="<?php print $user_agent;?>" type="text" id="user_agent" size="20">
  </td>
  <td colspan="3">
    <acronym title='This value is also relevant for "robots.txt" rules.'>
    User agent string
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_min_delay" value="<?php print $min_delay;?>" type="text" id="min_delay" size="5">
  </td>
  <td colspan="3">
    <acronym title="The minimum number of seconds that Sphider should wait between loading two pages.">
    Minimal delay between page downloads
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_stem_words" type="checkbox"  value="1" id="stem_words" <?php  if ($stem_words==1) echo "checked";?> >
  </td>
  <td colspan="3">
    <acronym title='For example, find sites containing "runs" and "running" when searching for "run".'>
    Use word stemming (currently implemented for English only); requires a re-index.
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_strip_sessids" type="checkbox"  value="1" id="strip_sessids" <?php  if ($strip_sessids==1) echo "checked";?> >
  </td>
  <td colspan="3">
    Strip session ids (PHPSESSID, JSESSIONID, ASPSESSIONID, sid).
  </td>
</tr>

<tr>
  <td colspan="4"><div class="tableSubHeading">Search settings</div></td>
</tr>

<tr>
  <td class="left1">
    <input type="radio" name="_results_per_page" value="10"<?php  if ($results_per_page==10) echo "checked";?> >10
    <input type="radio" name="_results_per_page" value="20"<?php  if ($results_per_page==20) echo "checked";?> >20
    <input type="radio" name="_results_per_page" value="50"<?php  if ($results_per_page==50) echo "checked";?> >50
  </td>
  <td colspan="3">
    Default results per page
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_cat_columns" type="text" id="cat_columns" value="<?php print $cat_columns;?>" size="5" maxlength="2">
  </td>
  <td colspan="3">
    <acronym title="If you increase this, you might also want to increase the category table in the CSS file.">
    Number of columns in category list
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_bound_search_result" type="text" value="<?php print $bound_search_result;?>" id="bound_search_results" size="5">
  </td>
  <td colspan="3">
    <acronym title='A low value can speed up searches on large database (i.e. a large site).'>
    Bound number of search results (set to 0 for no bound)
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_length_of_link_desc" type="text" value="<?php print $length_of_link_desc;?>" id="length_of_link_desc" size="5" maxlength="4">
  </td>
  <td colspan="3">
    <acronym title='A low value, like 250, can speed up searches on (very) slow machines.'>
    The maximum length of the description text for a page when displaying search results (set to 0 for unlimited)
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_show_meta_description" type="checkbox" value="1" id="show_meta_description" <?php if ($show_meta_description==1) echo "checked";?> >
  </td>
  <td colspan="3">
    Show meta description in results page if it exists (instead of an extract from the page text)
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_desc_length" type="text" id="desc_length" size="5" maxlength="4" value="<?php print $desc_length;?>">
  </td>
  <td colspan="3">
    <acronym title="Maximum line length in characters">
    Maximum length of page summary displayed in search results
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_links_to_next" type="text" value="<?php print $links_to_next;?>" id="links_to_next" size="5" maxlength="2">
  </td>
  <td colspan="3">
    Number of links shown to "next" pages
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_advanced_search" type="checkbox"  value="1" id="advanced_search" <?php if ($advanced_search==1) echo "checked";?> >
  </td>
  <td colspan="3">
    <acronym "Allows the use of AND and OR in search queries">
    Advanced search
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_show_query_scores" type="checkbox" value="1" id="show_query_scores" <?php if ($show_query_scores==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Show query scores
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_show_categories" type="checkbox" value="1" id="show_categories" <?php if ($show_categories==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Show categories
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_did_you_mean_enabled" type="checkbox" value="1" id="did_you_mean_enabled" <?php if ($did_you_mean_enabled==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Enable spelling suggestions (Did you mean...)
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_did_you_mean_always" type="checkbox" value="1" id="did_you_mean_always" <?php if ($did_you_mean_always==0) echo "checked";?>>
  </td>
  <td colspan="3">
    <acronym title="If disabled, but spelling suggestions are enabled, spelling suggestions will only be shown when there are no results for the keywords that the user typed.">
    Always search for alternative spellings, not just when there are no results
    </acronym>
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_merge_site_results" type="checkbox" value="1" id="merge_site_results" <?php if ($merge_site_results==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Show only the 2 most relevant links from each site
  </td>
</tr>

<tr>
  <td colspan="4"><div class="tableSubHeading">Suggest</div></td>
</tr>

<tr>
  <td class="left1">
    <input name="_suggest_enabled" type="checkbox" value="1" id="_suggest_enabled" <?php if ($suggest_enabled==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Enable Sphider Suggest
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_suggest_history" type="checkbox" value="1" id="_suggest_history" <?php if ($suggest_history==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Search for suggestions in query log
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_suggest_keywords" type="checkbox" value="1" id="_suggest_keywords" <?php if ($suggest_keywords==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Search for suggestions in keywords
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_suggest_phrases" type="checkbox" value="1" id="_suggest_phrases" <?php if ($suggest_phrases==1) echo "checked";?>>
  </td>
  <td colspan="3">
    Search for suggestions in phrases
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_suggest_rows" type="text" id="_suggest_rows" size="3" maxlength="2" value="<?php print $suggest_rows;?>">
  </td>
  <td colspan="3">
    Limit number of suggestions
  </td>
</tr>

<tr>
  <td colspan="4"><div class="tableSubHeading">Weights</div></td>
</tr>

<tr>
  <td class="left1">
    <input name="_title_weight" type="text" id="title_weight" size="5" maxlength="2" value="<?php print $title_weight;?>">
  </td>
  <td colspan="3">
    Relative weight of a word in the title of a webpage
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_domain_weight" type="text" id="domain_weight" size="5" maxlength="2" value="<?php print $domain_weight;?>">
  </td>
  <td colspan="3">
    Relative weight of a word in the domain name
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_path_weight" type="text" id="path_weight" size="5" maxlength="2" value="<?php print $path_weight;?>">
  </td>
  <td colspan="3">
    Relative weight of a word in the path name
  </td>
</tr>

<tr>
  <td class="left1">
    <input name="_meta_weight" type="text" id="meta_weight" size="5" maxlength="2" value="<?php print $meta_weight;?>">
  </td>
  <td colspan="3">
    Relative weight of a word in meta_keywords
  </td>
</tr>

<tr>
<td colspan="4" align="center"><br/> <input type="submit" value="Save settings" id="submit"></td>
</tr>

</table>
</form>

</div>
