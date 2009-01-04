<?php

	/*
	*  _________________________________________________
	*  $Id: util_lib.php 73 2006-08-29 20:41:15Z gstewart $
	*  main function library for tv plan
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  _________________________________________________
	*/

	function array_search_r($needle, $haystack)
	{
		$match = 0;

		foreach ($haystack as $value) {

			if (is_array($value)) {
				$match = array_search_r($needle, $value);
			}
			if ($value == $needle) {
				$match = 1;
			}
			if ($match) {
				return 1;
			}
		}

		return 0;

	}

	function array_split($in)
	{
		$keys = func_get_args();
		array_shift($keys);

		$out = array();
		foreach($keys as $key) {
			if (isset($in[$key])) {
				$out[$key] = $in[$key];
			}
			else {
				$out[$key] = null;
			}
			unset($in[$key]);
		}

		return $out;
	}

	function build_calendar($month, $year, $dateArray, $infoArray, $style, $offset)
	{

		$daysOfWeek = array ('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');
		$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);

		$numberDays = date('t', $firstDayOfMonth);
		$dateComponents = getdate($firstDayOfMonth);
		$dayOfWeek = $dateComponents['wday'];

		$currdate = date('mY', $offset);
		$specdate = $month . $year;

		$monthText = $dateComponents['month'];
		$yearText = $dateComponents['year'];

		$calendar = "
		<div class=\"calendar\">

		<!-- Calendar days heading -->
		<div class=\"row\">

		";

		/* create calendar headers */
		foreach ($daysOfWeek as $day) {
			$calendar .= "<span class=\"heading\">$day</span>";
		}

		/* create the rest of the calendar */
		$eventDay = 1;
		$calendar .= "</div><div class=\"row\">";

		/*
		* the variable $dayOfWeek is used to
		* ensure that the calendar
		* display consists of exactly 7 columns.
		*/
		$dayOfWeek2 = $dayOfWeek;
		while ($dayOfWeek2 > 0) {
			$calendar .= "<a href=\"index.html\" class=\"blankDay\"></a>\n";
			$dayOfWeek2 = $dayOfWeek2 - 1;
		}

		while ($eventDay <= $numberDays) {

			/* seventh column (saturday) reached, so start a new row. */
			if ($dayOfWeek == 7) {
				$dayOfWeek = 0;
				$calendar .= "</div>\n<div class=\"row\">\n";
			}

			/* if we've found an important day, add it to the mouseover! */
			if (@in_array($eventDay, $dateArray)) {

				foreach ($infoArray as $episode_info) {

					/* get the overlib data */
					if ($episode_info['date'] == $eventDay) {

						/* we've been before so don't just move on */
						if (!isset($beenbefore)) {

							$showArray[$eventDay][] = (array(
								"day"=>$eventDay,
								"name"=>$episode_info['name'],
								"show"=>$episode_info['show'],
								"number"=>$episode_info['episode'],
								"url"=>$episode_info['url']
							));

							$episode_url = $episode_info['url'];
							$all_episode_urls[] = $episode_info['url'];
						}
						else {
							$showArray[$eventDay][] = (
								array(
									"day"=>$eventDay,
									"name"=>$episode_info['name'],
									"show"=>$episode_info['show'],
									"number"=>$episode_info['episode'],
									"url"=>$episode_info['url'])
								);

								/* store all the urls and indicate that we've got multiple ones */
								$all_episode_urls[] = $episode_info['url'];
								$multi_urls = 1;
						}
						$beenbefore = 1;
					}
				}
				unset($beenbefore);

				/* open multiple urls with javascript */
				if (isset($multi_urls)) {
					$episode_url = "javascript:";
					foreach ($all_episode_urls as $each_url) {

						/* append the correct javascript for each url */
						$episode_url .= sprintf("void(window.open('%s'));", $each_url);
					}
				}

				$overlib_string = "";

				foreach($showArray as $episode_list) {
					foreach($episode_list as $episode) {

						/* create our funky overlib string converting all special html chars so the page validates */
						$overlib_string .= htmlspecialchars(sprintf("%s (%s) <a href=%s>%s</a><br />",
						$episode['show'],
						$episode['number'],
						$episode['url'],
						addslashes($episode['name'])
					));
					}
				}

				if (($eventDay == date('d', $offset)) && ($currdate==$specdate)) {

					$calendar .= sprintf("<a href=\"%s\" class=\"currDay\" %s>%s</a>\n",
					$episode_url,
					create_overlib($overlib_string, $style),
					$eventDay);

				}
				else {
					$calendar .= sprintf("<a href=\"%s\" class=\"eventDay\" %s>%s</a>\n",
					$episode_url,
					create_overlib($overlib_string, $style),
					$eventDay);
				}

				unset($overlib_string);
				unset($showArray);
				unset($all_episode_urls);
				unset($multi_urls);

			} else {

				/* check if the day is today */
				if (($eventDay == date('d', $offset)) && ($currdate == $specdate)) {
					$calendar .= "<a href=\"?\" class=\"currDay\">$eventDay</a>\n";
				} else {
					$calendar .= "<a href=\"?\" class=\"day\">$eventDay</a>\n";
				}

			}

			/* increment counters */
			$eventDay++;
			$dayOfWeek++;
		}

		$calendar .= "</div>";

		$month_prev = ($month-1);
		$month_next = ($month+1);

		$year_prev = ($year-1);
		$year_next = ($year+1);

		if ($month_prev == 0) {
			$month_prev =  12;
			$special_year = $year;
			$year = $year - 1;
		}

		elseif ($month_next == 13) {
			$month_next =  1;
			$special_year2 = $year;
			$year = $year + 1;
		}

		$calendar .= "
		<!-- Calendar month and prev/next buttons -->
		<div class=\"row\">
		<span class=\"month\">
		<a href=\"?date,$month,$year_prev\" class=\"prevYear\" title=\"prev year\">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		<a href=\"?date,$month_prev," . (isset($special_year2) ? $special_year2 : $year) . "\" class=\"prevMonth\" title=\"prev month\">&nbsp;&nbsp;&nbsp;&nbsp;</a>

		&nbsp;&nbsp; " . $monthText . " " . $yearText . " &nbsp;&nbsp;

		<a href=\"?date,$month_next," . (isset($special_year) ? $special_year : $year) . "\" class=\"nextMonth\" title=\"next month\">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		<a href=\"?date,$month,$year_next\" class=\"nextYear\" title=\"next year\">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		</span>
		</div>

		</div>
		";

		return $calendar;
	}

	function calendar($highlight, $month=0, $year=0, $style="overlib", $newzbinlink=0, $offset=0)
	{

		if (($month==0) || ($year==0)) {
			$dateComponents = getdate($offset);
			$month = $dateComponents['mon'];
			$year = $dateComponents['year'];
		}

		$dateArray = "";

		if (is_array($highlight)) {
			foreach ($highlight as $show) {
				foreach($show as $showname) {
					if (is_array($showname)) {
						foreach($showname as $showinfo) {

							/* we want newzbin links instead of tv.com ones so replace what we've got */
							if (!empty($newzbinlink)) {
								$showinfo['url'] = sprintf("http://www.newzbin.com/search/query/p/?q=%s+%s&Category=-1&searchFP=p", urlencode($show['show']), urlencode($showinfo['name']));
							}
							$dateArray[] = date('d',strtotime($showinfo['date']));
							$infoArray[] = array(
								"date"=>date('d',strtotime($showinfo['date'])),
								"name"=>$showinfo['name'],
								"show"=>$show['show'],
								"episode"=>$showinfo['episode'],
								"url"=>$showinfo['url']
							);
						}
					}
				}
			}
		}

		if (!isset($infoArray)) {
			$infoArray = "";
		}
		if (!isset($dateArray)) {
			$dateArray = "";
		}

		return build_calendar($month, $year, $dateArray, $infoArray, $style, $offset);
	}

	function cmp_time($a, $b)
	{

		$m1 = filemtime($a['full_path']);
		$m2 = filemtime($b['full_path']);

		return ($m1 - $m2);
	}

	function checkFormInput($username, $password, $email, &$msg)
	{
		/* trim any rubbish off the username/password */
		$username = trim($username);
		$password = trim($password);

		/* check all fields were entered */
		if (!$username || !$password || !$email) {
			$msg[] = array("text"=> "Error: You did not fill in a required field!", "type"=>"error");
			return false;
		}

		/* check username is valid */
		elseif (!validUsername($username)) {
			$msg[] = array("text"=>sprintf("Error: The username '%s' must have between %d and %d characters, lowercase not containing illegal characters.", cutText($username, 15), MIN_USERNAME_LENGTH, MAX_USERNAME_LENGTH), "type"=>"error");
			return false;
		}

		/* check password is valid */
		elseif (!validPassword($password)) {
			$msg[] = array("text"=>sprintf("Error: Your password must have between %d and %d characters, and must not contain illegal characters.", MIN_PASSWORD_LENGTH, MAX_PASSWORD_LENGTH), "type"=>"error");
			return false;
		}

		/* check email is valid */
		elseif (!validEmail($email)) {
			$msg[] = array("text"=>sprintf("Error: The email '%s' is not a valid email address.", $email), "type"=>"error");
			return false;
		}

		/* check if username is already in use */
		elseif (isUsernameTaken($username)) {
			$msg[] = array("text"=>sprintf("Error: The user '%s' already exists.", $username), "type"=>"error");
			return false;
		}

		/* check if email is already in use */
		elseif (isEmailTaken($email)) {
			$msg[] = array("text"=>sprintf("Error: The email '%s' already exists in the database.", $email), "type"=>"error");
			return false;
		}
		else {

			/* passed all tests, should be fine! */
			return true;
		}
	}

	function create_overlib ($text, $style="balloon")
	{
		return ($style == "balloon") ?
		"onmouseover=\"return overlib('$text',BUBBLE,BUBBLETYPE,'quotation',ADJBUBBLE, STICKY, MOUSEOFF, WRAP, CELLPAD, 5)\" onmouseout=\"return nd();\"" :
		"onmouseover=\"return overlib('$text', STICKY, MOUSEOFF, WRAP, CELLPAD, 5)\"";
	}

	function cuttext ($vtxt, $car=38)
	{

		return (strlen($vtxt) > $car) ? substr($vtxt, 0, $car) . "..." : $vtxt;
	}

	function generatePassword($length=8)
	{

		/* valid characters a password can contain */
		$chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
		$numChars = strlen($chars);

		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= substr($chars, rand(1, $numChars) - 1, 1);
		}
		return $string;
	}

	function getTip($tipfile) 
	{
		return ($tips = @file($tipfile)) ? $tips[array_rand($tips)] : "nothing";
	}

	function isIPIn($ip,$net,$mask)
	{
		$lnet = ip2long($net);
		$lip = ip2long($ip);
		$binnet = str_pad(decbin($lnet),32,"0","STR_PAD_LEFT");
		$firstpart = substr($binnet,0,$mask);
		$binip = str_pad(decbin($lip),32,"0","STR_PAD_LEFT");
		$firstip = substr($binip,0,$mask);

		return (strcmp($firstpart,$firstip) == 0);
	}

	function isPrivateIP($ip)
	{
		$privates = array(
			"127.0.0.0/24",
			"10.0.0.0/8",
			"172.16.0.0/12",
			"192.168.0.0/16"
		);

		foreach($privates as $k ) {
			list($net,$mask)=split("/",$k);
			if (isIPIn($ip,$net,$mask)) {
				return true;
			}
		}
		return false;
	}


	function isURL($string)
	{
		return (preg_match('#^([a-z0-9]+?)://#i',$string));
	}

	function logEvent($logfile)
	{

		/* ignore any local attempts */
		if (isPrivateIP($_SERVER['REMOTE_ADDR'])) {
			return true;
		}

		if ($fp = @fopen($logfile, 'a')) {

			flock($fp, 2);
			fwrite($fp, sprintf("%s\t%s\t\t%s\t%s\n",
			gmdate('Y-m-d H:i:s', time() + 3600*date("I")),
			@gethostbyaddr($_SERVER['REMOTE_ADDR']),
			$_SERVER['QUERY_STRING'],
			(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "direct")
		));
		fclose($fp);
		return true;
		}
		else {
			return false;
		}

	}

	function logOut()
	{

		if ((isset($_COOKIE['cookname'])) && (isset($_COOKIE['cookpass']))) {
			setcookie("cookname", "", time() - 60 * 60 * 24 * 100, "/");
			setcookie("cookpass", "", time() - 60 * 60 * 24 * 100, "/");
		}

		/* unset main session variables and destroy session */
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		$_SESSION = array ();
		session_destroy();
	}

	function multimerge ($array1, $array2)
	{

		if (is_array($array2) && count($array2)) {
			foreach ($array2 as $k => $v) {
				if (is_array($v) && count($v)) {
					$array1[$k] = multimerge($array1[$k], $v);
				}
				else {
					$array1[$k] = $v;
				}
			}
		}
		else {
			$array1 = $array2;
		}

		return $array1;
	}

	function ping($url)
	{
		$connect = @fopen($url, "r");
		@fclose($connect);

		return $connect;
	}

	function redirect($url = NULL, $relative = FALSE)
	{
		header("Location: http://".$_SERVER['HTTP_HOST'].(($relative) ? dirname($_SERVER['PHP_SELF'])."/" : "").$url);
		exit;
	}

	function removeEvilAttributes($tagSource)
	{
		$stripAttrib = "' (style|class)=\"(.*?)\"'i";
		$tagSource = stripslashes($tagSource);
		$tagSource = preg_replace($stripAttrib, '', $tagSource);
		return $tagSource;
	}

	function removeEvilTags($source)
	{
		$allowedTags='<b><br><p><strong><em><h1><h2><h3><h4><i><img><u>';
		$source = strip_tags($source, $allowedTags);
		return preg_replace('/<(.*?)>/ie', "'<'.removeEvilAttributes('\\1').'>'", $source);
	}

	function timeOffset($offset = null)
	{
		if (is_int($offset)) {
			$local_offset = $offset - TIMEZONE_SERVER_OFFSET;
			return time() + 3600 * $local_offset;
		}
		return time();
	}

	if (!function_exists('url_fopen'))
	{
		function url_fopen ($url)
		{
			$user_agent = "Mozilla/8.0 (Windows 2008 SP32 + 3patch)";
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
			curl_setopt ($ch, CURLOPT_COOKIEJAR, "cookie.txt");
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
			$string = curl_exec ($ch);
			curl_close($ch);
			return $string;
		}
	}

	function unset_by_val($needle,&$haystack)
	{
		while(($gotcha = array_search($needle,$haystack)) > -1) {
			unset($haystack[$gotcha]);
		}
	}

	function validEmail($email)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
		'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$quoted_pair = '\\x5c\\x00-\\x7f';
		$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
		$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
		$domain_ref = $atom;
		$sub_domain = "($domain_ref|$domain_literal)";
		$word = "($atom|$quoted_string)";
		$domain = "$sub_domain(\\x2e$sub_domain)*";
		$local_part = "$word(\\x2e$word)*";
		$addr_spec = "$local_part\\x40$domain";
		return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
	}

	function validExt($filename, $ext)
	{
		return (eregi("\.".$ext."$", $filename));
	}

	function validUsername($username)
	{
		if (strlen($username)>MAX_USERNAME_LENGTH || strlen($username)<MIN_USERNAME_LENGTH)
		return false;
		for ($count=0;$count<strlen($username);$count++) {
			if (((ord($username[$count])<ord('a')) || (ord($username[$count])>ord('z'))) &&
			((ord($username[$count])<ord('0')) || (ord($username[$count])>ord('9'))) &&
			($username[$count]!='_'))
			return false;
		}
		return true;
	}

	function validPassword($password)
	{
		if (strlen($password)>MAX_PASSWORD_LENGTH || strlen($password)<MIN_PASSWORD_LENGTH) {
			return false;
		}

		for ($count=0;$count<strlen($password);$count++) {

			/* allow lower and upper case for passwords */
			if (((ord($password[$count])<ord('A')) || (ord($password[$count])>ord('z'))) &&
			((ord($password[$count])<ord('0')) || (ord($password[$count])>ord('9'))) &&
			($password[$count]!='_'))
			return false;
		}

		return true;
	}

	function validUrl($url)
	{
		if (isUrl($url)) {
			$connect = @fopen($url, "r");
			@fclose($connect);
			return $connect;
		}
		return false;
	}

	function write_ini_file($path, $assoc_array)
	{
		$content = "";

		foreach ($assoc_array as $key => $item) {
			if (is_array($item)) {
				$content .= "\n[{$key}]\n";
				foreach ($item as $key2 => $item2) {
					if (is_numeric($item2) || is_bool($item2))
					$content .= "{$key2} = {$item2}\n";
					else
					$content .= "{$key2} = \"{$item2}\"\n";
				}
			} else {
				if (is_numeric($item) || is_bool($item))
				$content .= "{$key} = {$item}\n";
				else
				$content .= "{$key} = \"{$item}\"\n";
			}
		}

		if (!$handle = @fopen($path, 'w')) {
			return false;
		}

		if (!fwrite($handle, $content)) {
			return false;
		}

		fclose($handle);
		return true;
	}

?>
