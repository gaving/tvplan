<?php

function search_show($id,$search) {
	
	print "parent._ac_rpc('$id'";

	$handle = fopen('../../cfg/shows.txt', 'r');
	$counter = 0;
	$length = strlen($search);
	
	while (!feof($handle)) {
		
		$contents = fgets($handle, 4096);
		
		if (strncasecmp($contents,$search,$length) == 0) {
		
			/*
			<proper title><link><??>
			Against the Grain,AgainsttheGrain,nothing
			*/
			
			$line = explode(',', $contents);
			$name = $line[0];
			$link = rtrim($line[1]);
			
			print ',"'. $name .'","'. $link .'"';
			
			if ($counter++ > 10) {
				break;
			}
		}
	}
	print ");\n";
	
	fclose($handle);
}


header('Content-Type: text/html; charset=UTF-8');

if (isset($i)) {

	print "
		<html>
		<body>
		<script type=\"text/javascript\">
		<!--
		";

}

/* reference id */
$id = array_key_exists('id', $_REQUEST) ? trim($_REQUEST['id']) : null;

/* search query */
$search = array_key_exists('s', $_REQUEST) ? trim($_REQUEST['s']) : null;

/* type of search, or category */
$type = array_key_exists('t', $_REQUEST) ? trim($_REQUEST['t']) : null;

switch ($type) {

	case 'show': 
		search_show($id,$search); 
	break;
			
	default: 
	break;
		
}

if (isset($i)) {
	print "
		// -->
		</script>
		</body>
		</html>
	";
}


?>