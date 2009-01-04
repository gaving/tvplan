<?php

	/*
	*  _________________________________________________
	*  $Id: user_lib.php 48 2006-01-14 13:03:57Z gstewart $
	*  user function library for tv plan
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  _________________________________________________
	*/

	class User {

		private $username;
		private $password;
		private $email;
		private $admin;
		private $table_showindex;
		private $table_newsindex;
		private $filename_config;

		public function __construct()
		{

			$args = func_get_args();

			if (count($args) > 0) {
				$this->table_showindex = "tp_user_" . $args[0] . "_SHOWINDEX";
				$this->table_newsindex =  "tp_user_" . $args[0] . "_NEWSINDEX";
				$this->filename_config =  PROFILEDIR . $args[0] . ".ini";
			}

			switch (count($args))
			{
				case 1:
					$this->username = $args[0];
					break;
				case 2:
					$this->username = $args[0];
					$this->password = $args[1];
					break;
				case 3:
					$this->username = $args[0];
					$this->password = $args[1];
					$this->email = $args[2];
					$this->admin = 0;
					break;
				case 4:
					$this->username = $args[0];
					$this->password = $args[1];
					$this->email = $args[2];
					$this->admin = $args[3];
					break;
			}
		}

		public function add() 
		{
			global $mysqli;

			if ($stmt = $mysqli->prepare("INSERT INTO tp_users (username, password, email, admin, login)
			VALUES (?, MD5(?), ?, ?, NOW())")) {

				$stmt->bind_param("sssi", $this->username, 
				$this->password, $this->email, $this->admin);
				$stmt->execute();
				$stmt->close();
			}

			return (!$mysqli->error);
		}

		public function confirmAdmin()
		{
			global $mysqli;

			/* add slashes if necessary (for query) */
			if (!get_magic_quotes_gpc()) {
				$this->username = addslashes($this->username);
			}

			/* verify that user is in database */
			$result = $mysqli->query(sprintf("SELECT admin FROM tp_users WHERE username = '%s'", $this->username));

			if (!$result || ($result->num_rows) < 1) {
				return 0;
			}
			$dbarray = $result->fetch_assoc();
			$this->admin = $dbarray['admin'];

			/* validate if username is an admin */
			return ($dbarray['admin'] == '1');
		}

		public function confirmUser()
		{
			global $mysqli;

			$password = md5($this->password);

			$username = (!get_magic_quotes_gpc()) ? 
			addslashes($this->username) : $this->username;

			/* verify that user is in database */
			$result = $mysqli->query(sprintf("SELECT password FROM tp_users WHERE username = '%s'", $username));

			if (!$result || ($result->num_rows) < 1) {

				/* failure if user doesnt exist */
				return 1;
			}

			/* retrieve password from result, strip slashes */
			$dbarray = $result->fetch_assoc();

			if ($password == $dbarray['password']) {

				/* success! username and password confirmed */
				return 0;
			} else {

				/* password failure */
				return 2;
			}
		}

		public function createUserTables()
		{
			global $mysqli;

			$mysqli->query(sprintf("CREATE TABLE IF NOT EXISTS %s (id INT NOT NULL AUTO_INCREMENT, full_name varchar(32), short_name varchar(64), PRIMARY KEY(id))",$this->table_showindex));
			$mysqli->query(sprintf("CREATE TABLE IF NOT EXISTS %s (id INT NOT NULL AUTO_INCREMENT, date DATETIME, series varchar(32), title varchar(255), text text, PRIMARY KEY(id))",$this->table_newsindex));

			return (!$mysqli->error);
		}

		public function delete()
		{
			global $mysqli;

			/* delete user index and mysql tables */
			$mysqli->query(sprintf("DELETE FROM tp_users WHERE username='%s'", $this->username));
			$mysqli->query(sprintf("DROP TABLE IF EXISTS %s", $this->table_showindex));
			$mysqli->query(sprintf("DROP TABLE IF EXISTS %s", $this->table_newsindex));

			/* return success on deletion of config and no table errors */
			return ((!$mysqli->error) && @unlink($this->filename_config));
		}

		public function getActiveShowNames()
		{

			global $mysqli;

			$dateComponents = getdate();
			$month = $dateComponents['mon'];
			$year = $dateComponents['year'];
			$date_format = $year . "-" . $month . "-" . 01;

			$show_array = "";
			$result = $mysqli->query(sprintf("SELECT * from %s ORDER BY full_name", $this->table_showindex));

			while ($row = $result->fetch_assoc()) {

				$table = "tp_show_" . getSaneName($row["short_name"]);

				if ($result2 = $mysqli->query(sprintf("
				SELECT ABS(MIN(DATEDIFF('%s', `date`))) AS gah, `date`, episode, url, name
				FROM %s
				WHERE (`date` != '0000-00-00')
				AND (DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= `date`)
				GROUP BY `date` ORDER BY gah LIMIT 1", $date_format, $table))) {

					while ($row2 = $result2->fetch_assoc()) {
						$next_air = $row2['date'];
					}
				}

				$show_array[] = array(
					"name"=> $row['full_name'], 
					"epguide_name"=> $row['short_name'], 
					"next_air"=>((isset($next_air)) ? 
					$next_air : "Finished"));

					unset ($next_air);
			}

			return $show_array;

		}

		public function getCalendarDates($month, $year)
		{

			global $mysqli;

			$closest_date = NULL;

			$result = $mysqli->query(sprintf("SELECT * FROM %s", $this->table_showindex));
			while ($row = $result->fetch_assoc()) {
				$closest_date[] = array(
					"show"=>$row['full_name'],
					"date"=>findClosestDate(getSaneName($row["short_name"]), $month, $year),
				);
			}

			return $closest_date;
		}

		public function getConfigFilename()
		{
			return $this->filename_config;
		}

		public function getEmail()
		{
			return $this->email;
		}

		public function getEmailOrUsername($detail)
		{
			global $mysqli;

			/* takes a username or email and gives the other back */
			$result = $mysqli->query(sprintf("SELECT * FROM tp_users WHERE username='%s' or email='%s'", $detail, $detail));

			while ($row = $result->fetch_assoc()) {

				/* if an output matches the input, return the other output */
				return (($row['username'] == $detail) ? $row['email'] 
				: $row['username']);
			}

			return False;

		}

		public function getLastLogin($offset)
		{
			global $mysqli;

			/* fix time offset from global constant */
			$offset = $offset - TIMEZONE_SERVER_OFFSET;

			if ($result = $mysqli->query(sprintf("SELECT UNIX_TIMESTAMP(login) AS FORMATED_TIME FROM tp_users WHERE username='%s'", $this->username))) {

				$login_time = $result->fetch_assoc();

				if ($login_time['FORMATED_TIME']) {

					$last_login = $login_time['FORMATED_TIME'] + (3600*$offset);
					$now = mktime(0,0,0,date('m'),date('d'),date('Y'));

					/* get number of days */
					$total_days = 0;
					while ($last_login < $now) { 
						$total_days++; 
						$last_login += 86400;
					}

					return $total_days;
				}
			}

			return NULL;
		}
		
		public function getNews()
		{
			global $mysqli;

			$posts = NULL;
			$result = $mysqli->query(sprintf("SELECT * from %s ORDER BY date DESC LIMIT 10", $this->table_newsindex));
			while ($row = $result->fetch_assoc()) {

				/*
				* since validation can be broken by stupid non-escaped input,
				* try and escape without being too crypic
				*/
				$posts[] = array(
					"id"=>$row['id'],
					"date"=>$row['date'],
					"show"=>htmlentities($row['series'], ENT_QUOTES),
					"title"=>htmlentities($row['title'], ENT_QUOTES),
					"body"=>nl2br($row['text'])
				);
			}

			return $posts;

		}
		
		public function getNewsIndex()
		{
			return $this->table_newsindex;
		}
		
		public function getOtherName($show)
		{

			global $mysqli;

			$result = $mysqli->query(sprintf("SELECT * FROM %s WHERE full_name='%s' OR short_name='%s'", $this->table_showindex, $show, $show));

			while ($row = $result->fetch_assoc()) {
				return (($row['short_name'] == $show) ? $row['full_name'] : $row['short_name']);
			}

			return False;
		}

		public function getPassword()
		{
			return $this->password;
		}

		public function getShowNames()
		{

			global $mysqli;

			$show_array = NULL;
			$result = $mysqli->query(sprintf("SELECT * from %s ORDER BY full_name", $this->table_showindex));
			while ($row = $result->fetch_assoc()) {
				$show_array[] = $row['full_name'];
			}

			return $show_array;

		}

		public function getShowIndex()
		{
			return $this->table_showindex;
		}

		public function getUsername()
		{
			return $this->username;
		}

		public function resetPassword()
		{
			global $mysqli;

			/* generate a new password for the user */
			$password = generatePassword();

			$mysqli->query(sprintf("UPDATE tp_users SET password ='%s' WHERE username = '%s' OR email = '%s'",md5($password), $this->getUsername(), $this->getEmail()));
			return (!$mysqli->error);
		}

		public function sendMail($from_email, $from_name, $subject, $body)
		{
			if (!ENABLEEMAILS)
			return;

			return (mail($this->email, $subject, stripslashes(nl2br($body)),
			"From: $from_name <$from_email>\n" .
			"Reply-To: $from_email\n" .
			"Return-Path: $from_email <$from_email>\n" .
			"MIME-Version: 1.0r\n" .
			"X-Mailer: PHP/" . phpversion() . "\n" .
			"Content-type: text/html; charset=ISO-8859-1\n"));
		}

		public function setAdmin($admin)
		{
			$this->admin = $admin;
		}

		public function setEmail($email)
		{
			$this->email = $email;
		}

		public function setPassword($password)
		{
			$this->password = $password;
		}

		public function setUsername($username)
		{
			$this->username = $username;
		}

		public function updateLastLogin()
		{
			global $mysqli;

			$stmt =  $mysqli->stmt_init();
			if ($stmt->prepare("UPDATE tp_users SET login = NOW() WHERE username=?")) {
				$stmt->bind_param("s", $this->username);
				$stmt->execute();
				$stmt->close();
			}

			return (!$mysqli->error);
		}

		public function updatePassword($password)
		{
			global $mysqli;

			$stmt = $mysqli->stmt_init();
			if ($stmt->prepare("UPDATE tp_users SET password = '?' WHERE username='?'")) {

				$stmt->bind_param("ss", md5($password), $this->username);
				$stmt->execute();
				$stmt->close();
			}

			return (!$mysqli->error);
		}

		public function write_default_config()
		{

			/* return the config */
			return (write_ini_file($this->filename_config,
			array(
				"main"=>
				array(
					"checknews"=>1,
					"checkrss"=>0,
					"showsearchbox"=>0,
					"calendarstyle"=>"overlib",
					"offset"=>"+0",
					"newzbinlinks"=>0,
					"enabletips"=>1
				),
				"rss"=>
				array(
					"server"=>"remote/path/to/feed.xml",
					"username"=>"whatever",
					"password"=>"ifneeded",
				),
				"torrent"=>
				array(
					"showtorrents"=>0,
					"showseeding"=>0,
					"torrentfile"=>0,
				)
			)));
		}

		public function write_updated_config($updated_config_file)
		{
			return (!is_file($this->filename_config)) ? False : 
				(write_ini_file($this->filename_config, $updated_config_file));
		}

	}
?>
