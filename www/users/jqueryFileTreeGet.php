<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

$_GET['dir'] = urldecode($_GET['dir']);
$dir = str_replace(".","",$_GET['dir']);
if (!startsWith($dir,"/var/www/html/Replays/")) {
	die('Wrong directory '.$dir.'!');
}
$_GET['dir'] = $dir;

if( file_exists($_GET['dir']) ) {
	$files = scandir($_GET['dir']);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All files
		foreach( $files as $file ) {
			if( $file != '.' && $file != '..' && !is_dir($_GET['dir'] . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
				echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_GET['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
			}
		}
		echo "</ul>";
	} else {
		echo 'Empty.';
	}
}

?>
