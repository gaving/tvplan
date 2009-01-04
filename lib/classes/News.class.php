<?php

	/*
	*  _________________________________________________
	*  $Id: tv.com_lib.php 52 2006-01-22 10:52:42Z gstewart $
	*  main news class (tv.com) 
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  _________________________________________________
	*/

	class News {

		private $url;
		private $show;
		private $title;
		private $user;

		public function __construct()
		{

			$args = func_get_args();

			switch (count($args))
			{
				case 1:
					$this->user = $args[0];
				break;
				case 4:
					$this->user = $args[0];
					$this->url = $args[1];
					$this->show = $args[2];
					$this->title = $args[3];
				break;
			}
		}

		public function add()
		{

			/* read the entire contents and array up each line, terminating on error */
			if (!$body_array = $this->readNewsContents()) {
				return False;
			}

			/* clear up our text */
			$body_array = $this->clearUpNewsContents($body_array);

			/* insert all our valid information from the array into the mysql database */
			return ($this->insertInfoToNewsTable($body_array));
		}

		private function clearNewsTable()
		{
			global $mysqli;

			$mysqli->query(sprintf("CREATE TABLE IF NOT EXISTS %s (id INT NOT NULL AUTO_INCREMENT, date DATETIME, series varchar(32), title varchar(255), text text, PRIMARY KEY(id))", 
				$this->user->getNewsIndex()));
			return (!$mysqli->error);
		}

		private function clearUpNewsContents($body_array)
		{

			/* cleans up our raw array of random garbage */
			foreach ($body_array as $body_element) {

				/* clean up the lines */
				$body_element = trim($body_element);

				/* add to the array if the element isnt empty */
				if (!empty($body_element)) {
					$body_array2[] = removeEvilTags($body_element);
				}
			}

			return $body_array2;
		}

		public function deleteShowFromNews($id) 
		{
			global $mysqli;

			$mysqli->query(sprintf("DELETE FROM %s WHERE id = '%s'", 
			$this->user->getNewsIndex(), $id));
			return (!$mysqli->error);
		}


		public function getTitle()
		{
			return $this->title;
		}

		public function hasNews()
		{
			global $mysqli;

			$result = $mysqli->query(sprintf("SELECT * from %s WHERE (series = '%s' and title = '%s')",
			$this->user->getNewsIndex(), $this->show, $this->title));

			return (($result) ? ($result->num_rows > 0) : False);
		}

		public function insertInfoToNewsTable($body_array2)
		{
			global $mysqli;

			$entire_body = "";

			$this->clearNewsTable();

			/* loop through our lines getting all important info */
			foreach ($body_array2 as $body_line) {

				/* add our img class for the raw images */
				$body_line = str_replace("<img", "<img class=\"imgSpace\"", $body_line);

				/* remove needless 'watch video' text */
				$body_line = str_replace("watch video", "", $body_line);

				$entire_body .= $body_line;
			}

			$mysqli->query(sprintf("INSERT INTO %s (id, date, series, title, text) values (NULL, NOW(), '%s', '%s', '%s')",
			$this->user->getNewsIndex(),
			$this->show,
			$mysqli->real_escape_string($this->title),
			$mysqli->real_escape_string($entire_body)));

			echo $mysqli->error;
			return (!$mysqli->error);

		}

		function nuke() 
		{
			global $mysqli;

			$mysqli->query(sprintf("DELETE from %s where 1>0", 
			$this->user->getNewsIndex()));
			return (!$mysqli->error);
		}

		function readNewsContents()
		{

			/* check if we're using curl */
			if (ENABLE_CURL) {
				if (!$contents = url_fopen($this->url)) {
					return False;
				}
			}
			else {
				if (!$handle = fopen($this->url, 'r')) {
					return False;
				}

				$contents = "";

				/* loop through our page creating a massive string */
				while (!feof($handle)) {
					$contents .= fread($handle, "100000000");
				}
				fclose($handle);
			}

			$contents = substr($contents, strpos($contents, "<div id=\"main-col\">"), 100000000);
			$contents = substr($contents, 15, (strpos($contents, "<div class=\"divider\"></div>")));

			/* split our file into an array element for each line */
			$season_array = explode("\n", $contents);

			return $season_array;

		}


	}

?>
