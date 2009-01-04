<?php

// _______________________________
// grab_shows.php 0.3
//
// Grab epguide data
// Gavin Gilmour
// Created: 30/04/05
// Info:
//	Populates 'shows' for tvplan with valid info
//	pretty risky to run as it requests
//	~26 various pages from a server
// _______________________________
// Revision:
// 	.1 : 01/05/05 - started
//  .2 : 11/08/05 - revised output and timeout
//  .3 : 26/09/05 - modified due to epguides.com change
// _______________________________

/* populating array will take a while */
ini_set('max_execution_time','6000');

define ('TIMEOUT', '2'); // timeout (seconds) between grabbing next page
define ('ENABLE_CURL', false); // use curl instead
define ('SHOWLIST', '../cfg/shows.txt'); // file to write to

function holdUp ($text, $error=0) {
	if (!empty($error)) {
		die("<strong>Error: </strong>" . $text . ".\n");
	}
	else {
		print $text . "...<br />" . "\n";
		@ob_flush();
		flush();
		sleep(2);
	}
}

/* back up old list */
holdUp("Checking backup");
if (file_exists(SHOWLIST)) {
    holdUp("Old shows.txt found, renaming");
    $newname = dirname(SHOWLIST) . "/" . basename(SHOWLIST, ".txt") . date("dmY", filemtime(SHOWLIST));

    if ($fp = @fopen($newname, "w+")) {
        if (copy(SHOWLIST, $newname)) {
            holdUp("Success!");
	        @unlink(SHOWLIST);
        }
        else {
            holdUp("Copy failed!", 1);
        }
        fclose($fp);
    }
    else {
        holdUp(sprintf("Could not write to '%s'", $newname), 1);
    }
}

/* 
holdUp("Checking file permissions");

if ($fp = @fopen(SHOWLIST, "w+")) {
	@unlink(SHOWLIST);
	fclose($fp);
}
else {
	holdUp(sprintf("Could not write to '%s'", SHOWLIST), 1);
}
*/

/* base url (epguides.com, obviously) */
$url = "http://www.epguides.com/";

/* lets get started */
holdUp("Grabbing index page");

if (ENABLE_CURL) {
	if (!$contents=url_fopen($url)) {
		die("URL not found, aborting.");
	}
}
else {
	if (!$handle=fopen($url, 'r')) {
		die("URL not found, aborting.");
	}
	$contents = "";

	while (!feof($handle)) {
		$contents .= fread($handle, "100000000");
	}
	fclose($handle);
}

$contents = substr($contents,
					(strpos($contents, "<font size=-1>Full Menus for All Series (by first letter)<br>") +
					strlen("<font size=-1>Full Menus for All Series (by first letter)<br>")), 100000000);
$contents = substr($contents, 0, strpos($contents, "</font>"));

/* split our file into an array element for each line */
//$menu_list = str_replace("\r", "\n", $contents);
$menu_list = explode("\r", $contents);

/* cleans up our raw array of random garbage */
foreach ($menu_list as $menu_item) {

	/* clean up the lines */
	$menu_item = trim($menu_item);

	/* add to the array if the element isnt empty */
	if (!empty($menu_item)) {

			if ($info = preg_match('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', $menu_item, $huh)) {

				$link = $huh[1];
				$name = $huh[2];

				if (substr($link, 0, 4) == "menu") {
					$menu_list2[] = array("link"=>$link, "name"=>$name);
				}
			}
	}
}

holdUp("Recieved menu list");
holdUp("Populating array");

foreach ($menu_list2 as $menu_letter) {

	$url = "http://www.epguides.com/" . $menu_letter['link'];

	if (ENABLE_CURL) {
		if (!$contents=url_fopen($url)) {
			die("URL not found, aborting.");
		}
	}
	else {
		if (!$handle=fopen($url, 'r')) {
			die("URL not found, aborting.");
		}

		$contents = "";

		while (!feof($handle)) {
			$contents .= fread($handle, "100000000");
		}
		fclose($handle);
	}

	holdUp("	-> " . $menu_letter['link']);
	sleep(TIMEOUT);

	/* for some bizzare reason the table widths are different for these letters */
	if (($menu_letter['name'] == 'H') || ($menu_letter['name'] == 'Q') || ($menu_letter['name'] == 'X')) {

		/* grab the entire data from these two points */
		$contents = substr($contents,
						(strpos($contents, "<td valign=top width=270><font face=\"verdana,arial\">") +
						strlen("<td valign=top width=270><font face=\"verdana,arial\">")), 100000000);

	}
	else {

		$contents = substr($contents,
						(strpos($contents, "<td valign=top width=300><font face=\"verdana,arial\">") +
						strlen("<td valign=top width=300><font face=\"verdana,arial\">")), 100000000);
	}

	$contents = substr($contents, 0, strpos($contents, "</td></tr>"));

	/* split our file into an array element for each line */
	$shows = explode("<br>", $contents);

	/* cleans up our raw array of random garbage */
	foreach ($shows as $show) {

		/* clean up the lines */
		$show = trim($show);
		$show = strip_tags($show, "<a>");

		/* add to the array if the element isnt empty */
		if (!empty($show)) {

			if ($info = preg_match('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', $show, $huh)) {

				$link = $huh[1];
				$name = $huh[2];

				$entire_show_list[] = array("link"=>trim($link), "name"=>trim($name));
			}
		}
	}


}

/* finally write out file */
holdUp("Writing file");

if ($fp = fopen(SHOWLIST, "a")) {

	foreach ($entire_show_list as $each_show) {

		$link = ltrim(strrchr(rtrim($each_show['link'],'/'),'.'),'./');
		$name = str_replace(",", "&#44;", $each_show['name']);

		fputs($fp, "$name,$link\n");
	}

	fclose($fp);
	print "<strong>Done.</strong>";

}
else {

	/* panic! */

	/* print out array so we've don't lost everything after all that */
	print_R($entire_show_list);
	holdUp(sprintf("Could not write to '%s' despite saying I could!", SHOWLIST), 1);
}


?>
