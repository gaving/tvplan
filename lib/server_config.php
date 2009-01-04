<?php

	/*
	*  _________________________________________________
	*  $Id: server_config.template 73 2006-08-29 20:41:15Z gstewart $
	*  general config file 
	*
	*  Gavin Gilmour (gavin(at)brokentrain.net)
	*  great dominions they don't come cheap
	*  REFER TO README
	*  _________________________________________________
	*/

	/* essential constants */
	define('ENABLE_CURL', false); // use curl instead of fopen for urls
	define('MODREWRITE', '0'); // bother with mod_rewrite
	define('ENABLE_REGISTRATION', true); // allow people to register
	define('TIMEZONE_SERVER_OFFSET', 0); // your server's offset from GMT 0

	/* server mysql details */
	define('MYSQL_SERVER', 'mysql.someserver.net');
	define('MYSQL_DB', 'tvplan');
	define('MYSQL_USERNAME', 'user');
	define('MYSQL_PASSWORD', 'password');

?>
