<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">

	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<title>{$title}: {$desc}</title>
		<link rel="stylesheet" type="text/css" href="templates/stylesheet.css" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="icon" href="favicon.ico" />
		{if ($loadac)}
		<link rel="stylesheet" type="text/css" href="js/ac/ac.css" />
		<script type="text/javascript" src="js/ac/ac.js"></script>
		<script type="text/javascript" src="js/ac/acspec.js"></script>
		<script type="text/javascript" src="js/disableform.js"></script>
		{/if}<script type="text/javascript" src="js/overlib/overlib.js"></script>
		<script type="text/javascript" src="js/overlib/overlib_bubble.js"></script>
		<script type="text/javascript" src="js/general.js"></script>
		{if ($js_validate)}<script type="text/javascript" src="js/formval.js"></script>{/if}
	</head>

	<body{if ($loadac)} onload="setup(); init({$showtorrents_state}, {$enablerss_state});"{/if}>

		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

		<!-- Main site container starts -->
		<div id="siteBox">
