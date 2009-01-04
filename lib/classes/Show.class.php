<?php

	/*
	*  _________________________________________________
	*  $Id: epguide_lib.php 62 2006-04-09 18:28:42Z gstewart $
	*  main show class 
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  _________________________________________________
	*/

	class Show {

		private $epguide_name;
		private $real_name;
		private $table_name;
		private $user;

		public function __construct()
		{

			$args = func_get_args();

			switch (count($args))
			{
				case 3:
					if ($args[0]) {
						$this->epguide_name = $args[2];
						$this->table_name = getSaneName($args[2]);
					} else {
						$this->real_name = $args[2];
					}
					$this->user = $args[1];
				break;

				case 4:
					$this->user = $args[1];
					$this->epguide_name = $args[2];
					$this->table_name = getSaneName($args[2]);
					$this->real_name = $args[3];
				break;

			}
		}

		public function add()
		{

			if ($this->isShow()) {
				return ($this->update());
			}

			$url = "http://www.epguides.com/" . $this->epguide_name;

			/* read the entire contents and array up each line, terminating on error */
			if (!$season_array = $this->readContents($url)) {
				return False;
			}

			/* clean up each line from various whitespace, linebreaks etc. */
			$title = $season_array['title'];
			$season_array2 = $this->clearUpContents($season_array['text']);

			$this->real_name = $title;

			/* insert all our valid information from the array into the mysql database */
			return ($this->insertInfoToTable($season_array2));

		}

		private function addEpisode($date, $episode, $url, $name)
		{

			global $mysqli;

			$table = "tp_show_" . $this->table_name;

			/* add the episode into the table, remembering to escape the name since episodes could contain apostrophes */
			$mysqli->query(sprintf("INSERT INTO %s (date, episode, url, name) 
			VALUES ('%s', '%s', '%s', '%s')", $table, $date, $episode, $url, 
			$mysqli->real_escape_string($name)));

			return (!$mysqli->error);
		}

		private function clearRedundantShow() 
		{
			global $mysqli;

			$result = $mysqli->query("SELECT * FROM tp_users");
			$users = $result->num_rows;

			while ($row = $result->fetch_assoc()) {

				$result2 = $mysqli->query(sprintf("SELECT * FROM " . "tp_user_" . $row['username'] . "_SHOWINDEX WHERE short_name='%s'", $this->epguide_name));

				/* somebody is tracking this show, abort! */
				if (($result2->num_rows > 0)) {
					return False;
				}

			}

			$table = "tp_show_" . $this->epguide_name;

			/* nobody seems to be tracking the show, drop the table */
			$mysqli->query(sprintf("DROP TABLE IF EXISTS %s", $table));
			return (!$mysqli->error);

		}

		private function clearTable()
		{

			global $mysqli;

			$table = "tp_show_" . $this->table_name;

			/* make sure an 'index table' exists, and insert one */
			$mysqli->query(sprintf("CREATE TABLE IF NOT EXISTS %s (id INT NOT NULL AUTO_INCREMENT, full_name varchar(32), short_name varchar(64), PRIMARY KEY(id))", $this->user->getShowIndex()));
			$mysqli->query(sprintf("INSERT INTO %s (full_name, short_name) VALUES ('%s','%s')", $this->user->getShowIndex(), $this->real_name,$this->epguide_name));

			/* drop the table if it exists, and recreate it */
			$mysqli->query(sprintf("DROP TABLE IF EXISTS %s", $table));
			$mysqli->query(sprintf("CREATE TABLE %s (id INT NOT NULL AUTO_INCREMENT, date DATE, episode VARCHAR(16), url text, name VARCHAR(64), PRIMARY KEY(id))", $table));

			return (!$mysqli->error);
		}

		private function clearUpContents($season_array)
		{

			/* cleans up our raw array of random garbage */
			foreach ($season_array as $season_element) {

				/* clean up the lines */
				$season_element = strip_tags(trim($season_element), "<a>");

				/* add to the array if the element isnt empty */
				if (!empty($season_element)) {
					$season_array2[] = $season_element;
				}
			}

			return $season_array2;

		}

		public function delete()
		{

			global $mysqli;

			/* get our physical table name */
			$result = $mysqli->query(sprintf("SELECT short_name FROM %s WHERE full_name='%s'", 
				$this->user->getShowIndex(), $this->real_name));

			while ($row = $result->fetch_assoc()) {

				$this->setEpguideName($row['short_name']);

				/* remove our name in the index */
				$mysqli->query(sprintf("DELETE FROM %s WHERE short_name='%s'", $this->user->getShowIndex(), $this->getEpguideName()));

				/* drop the show if its redundant (nobody else using it); */
				$this->clearRedundantShow();

			}

			return (!$mysqli->error);

		}

		public function getEpguideName() 
		{
			return $this->epguide_name;
		}

		public function getClosestDate() 
		{
			global $mysqli;

			$dateComponents = getdate();
			$month = $dateComponents['mon'];
			$year = $dateComponents['year'];
			$date_format = $year . "-" . $month . "-" . 01;

			if ($result = $mysqli->query(sprintf("
				SELECT ABS(MIN(DATEDIFF('%s', `date`))) AS gah, `date`, episode, url, name
				FROM %s
				WHERE (`date` != '0000-00-00')
				AND (DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= `date`)
				GROUP BY `date` ORDER BY gah LIMIT 1", 
				$date_format, "tp_show_" . $this->epguide_name))) {
				
				/* dirty hack ^^ need some sort of get translation routines */
				while ($row = $result->fetch_assoc()) {
					$date = $row['date'];
				}
			}

			return ((!$mysqli->error) ? 
			((isset($date)) ? $date : "finished") : "false");
		}

		public function getRealName() 
		{
			return $this->real_name;
		}

		public function getShowData()
		{
			global $mysqli;

			/* get our physical table name */
			if ($result = $mysqli->query(sprintf("SELECT * FROM %s WHERE full_name='%s' OR short_name='%s'", 
			$this->user->getShowIndex(), $this->real_name, $this->epguide_name))) {

				while ($row = $result->fetch_assoc()) {

					$show_info[] = array(
						"show_name"=>$this->real_name, 
						"show_link"=>$row['short_name']);

					$table = "tp_show_" . getSaneName($row['short_name']);

					$result2 = $mysqli->query(sprintf("SELECT * from %s", $table));

					while ($row2 = $result2->fetch_assoc()) {
						if (($row2['date']) && ($row2['episode']) && ($row2['name']) && ($row2['url'])) {
							$show_info[] = array(
								"date"=>$row2['date'],
								"episode"=>$row2['episode'],
								"name"=>$row2['name'],
								"url"=>$row2['url']
							);
						}
					}
				}
			}

			return $show_info;

		}

		public function isShow()
		{
			global $mysqli;

			$result = $mysqli->query(sprintf("SELECT * FROM %s WHERE full_name='%s' OR short_name='%s'", 
			$this->user->getShowIndex(), $this->real_name, $this->epguide_name));

			return ($result->num_rows > 0);
		}

		private function insertInfoToTable($season_array2)
		{

			if (!$this->clearTable()) {
				return False;
			}

			/* loop through our lines getting all important info */
			foreach ($season_array2 as $season_element) {

				/* the raw url of the shows link to tv.com */
				$episode_raw_url = substr($season_element, strpos($season_element, "<a"), strpos($season_element, "</a>"));

				/* can get various information about the show episode from it */
				$episode_url = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\1', $episode_raw_url);
				$episode_name = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2', $episode_raw_url);

				/* grab the entire string up until the URL */
				$season_element = substr($season_element, 0, strpos($season_element, "<a"));

				/* replace the lines whitespace with a single space */
				$season_element = preg_replace("/\s+/", " ", trim($season_element));

				$exp = "/^((\d+)\.|<li>)\s(\d+- ?\d+)\s+(.*?)\s+(\d{1,2} \w{3} \d{2})$/";
				$exp2 = "/^((\d+)\.|<li>)\s(\d+- ?\d+)\s+(\d{1,2} \w{3} \d{2})$/";

				if (isset($episode_number)) unset($episode_number);
				if (isset($episode_date)) unset($episode_date);

				$results = "";
				$results2 = "";

				if (preg_match($exp, $season_element, $results)) {

					$episode_number = $results[3];
					$episode_number = ereg_replace(" ", "", $episode_number);
					$episode_date = $results[5];
				}

				/* check the second match for more information */
				elseif (preg_match($exp2, $season_element, $results2)) {

					$episode_number = $results2[3];
					$episode_number = ereg_replace(" ", "", $episode_number);
					$episode_date = $results2[4];

				}

				/* need a date and number */
				if ((!empty($episode_date)) && (!empty($episode_number))) {

					$show_added = 1;

					/* fix any malformed dates into just a null mysql date value */
					if (!$correct_date = @date("Y-m-d", strtotime($episode_date))) {
						$correct_date = "0000-00-00";
					}

					/* check for the url and name cos some shows dont have links! */

					/* add the episode to the table converting that crazy timestamp to standard mysql format */
					$this->addEpisode($correct_date, $episode_number, $episode_url, $episode_name);
				}
			}

			return ($show_added) ? True : False;
		}

		private function readContents($url)
		{

			/* check if we're using curl */
			if (ENABLE_CURL) {
				if (!$contents = url_fopen($url)) {
					return False;
				}
			}
			else {
				if (!$handle = fopen($url, 'r')) {
					return False;
				}

				$contents = "";

				/* loop through our page creating a massive string */
				while (!feof($handle)) {
					$contents .= fread($handle, "100000000");
				}
				fclose($handle);
			}

			$title = substr($contents, strpos($contents, "<title>"), 100);
			$title = substr($title, 0, strpos($contents, "</title>"));
			$title = trim(str_replace("(a Titles and Air Dates Guide)", "",strip_tags($title)));

			/* grab the entire data from these two points "-"x44 and closing pre tag. */
			$contents = substr($contents, strpos($contents, str_repeat("_", 43)), 100000000);
			$contents = substr($contents, 0, strpos($contents, "</pre>"));

			/* some pages are using carridge returns instead of newlines, so fix that */
			$contents = str_replace("\r", "\n", $contents);

			/* split our file into an array element for each line */
			$season_array = explode("\n", $contents);

			return (array("title"=>$title, "text"=>$season_array));

		}

		private function setEpguideName($name)
		{
			$this->epguide_name = $name;
		}

		private function setRealName($name)
		{
			$this->real_name = $name;
		}

		public function update()
		{
			return ($this->delete()) ? $this->add() : False;
		}
	}
?>
