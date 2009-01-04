<?php
	
	/*
	*  _________________________________________________
	*  $Id: epguide_lib.php 62 2006-04-09 18:28:42Z gstewart $
	*  general purpose user functions
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  _________________________________________________
	*/

	function checkLogin()
	{

		/* check if user has been remembered */
		if (isset ($_COOKIE['cookname']) && isset ($_COOKIE['cookpass'])) {
			$_SESSION['username'] = $_COOKIE['cookname'];
			$_SESSION['password'] = $_COOKIE['cookpass'];
		}

		/* username and password have been set */

		if (isset($_SESSION['username']) && isset($_SESSION['password'])) {

			$user = new User($_SESSION['username'], $_SESSION['password']);

			/* confirm that username and password are valid */
			if ($user->confirmUser() != 0) {
				/* variables are incorrect, user not logged in */
				unset ($_SESSION['username']);
				unset ($_SESSION['password']);
				return false;
			}
			return true;
		}

		/* user not logged in */
		else {
			return false;
		}
	}

	function isUsernameTaken($username)
	{
		global $mysqli;

		/* sanity check that we're not processing any insecure input */
		if (!get_magic_quotes_gpc()) {
			$username = addslashes($username);
		}

		if ($result = $mysqli->query(sprintf("SELECT username FROM tp_users WHERE username = '%s'", $username))) {
			return ($result->num_rows > 0);
		}
		return False;
	}

	function isEmailTaken($email)
	{
		global $mysqli;

		/* sanity check that we're not processing any insecure input */
		if (!get_magic_quotes_gpc()) {
			$email = addslashes($email);
		}

		if ($result = $mysqli->query(sprintf("SELECT email FROM tp_users WHERE email = '%s'", $email))) {
			return ($result->num_rows > 0);
		}
		return False;
	}

	function listUsers()
	{
		global $mysqli;

		if ($result = $mysqli->query("SELECT username FROM tp_users")) {
			while ($row = $result->fetch_assoc()) {
				$user_list[] = $row["username"];
			}
		}
		return $user_list;
	}
?>
