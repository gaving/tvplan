<?php
	
	/*
	*  _________________________________________________
	*  $Id: epguide_lib.php 62 2006-04-09 18:28:42Z gstewart $
	*  general purpose show functions
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  _________________________________________________
	*/
	
	function findClosestDate($table, $month, $year)
	{

		global $mysqli;

		$datearray = NULL;
		$date_format = $year . "-" . $month . "-" . 01;
		$table = "tp_show_" . $table;

		if ($result = $mysqli->query(sprintf("
		SELECT *
		FROM %s
		WHERE (date != '0000-00-00')
		AND (DATE_FORMAT(date,'%%m-%%y'))
		BETWEEN (DATE_FORMAT('%s','%%m-%%y'))
		AND (DATE_FORMAT('%s','%%m-%%y'))", $table, $date_format, $date_format))) {

			while ($row = $result->fetch_assoc()) {
				$datearray[] = $row;
			}
		}

		return $datearray;
	}

	function getSaneName($name)
	{
		return (!is_numeric($name)) ? $name : $name . "_table";			
	}
		
	function getSiteStats()
	{

		global $mysqli;

		/* simple show stats */
		$lines = file(SHOWLIST);
		$available_shows = count($lines);
		$tracked_shows = NULL;

		if ($result = $mysqli->query("SELECT * FROM tp_users")) {

			$users = $result->num_rows;

			/* query each individual user */
			while ($row = $result->fetch_assoc()) {

				$user_show_index = "tp_user_" . $row['username'] . "_SHOWINDEX";

				if ($result2 = $mysqli->query(sprintf("SELECT short_name FROM %s", $user_show_index))) {
					while ($row2 = $result2->fetch_assoc())
						$tracked_shows[] = $row2['short_name'];
				}
			}

			$result->close();
		}

		$tracked_shows = (is_array($tracked_shows)) ? count(array_unique($tracked_shows)) : 0;

		return array(
			"available_shows"=>$available_shows, 
			"tracked_shows"=>$tracked_shows, 
			"users"=>$users
		);

	}

	function isEpguideName($show)
	{

		global $mysqli;

		$result = $mysqli->query(sprintf("SELECT short_name FROM %s WHERE full_name='%s' OR short_name='%s'", 
		INDEXTABLE, $show, $show));

		return ($result->num_rows > 0);

	}

?>
