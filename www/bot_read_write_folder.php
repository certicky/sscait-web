<?php
session_start();

//===============================================
// DB connection & other settings
//===============================================
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

// check the GET parameters
if (
	!isset($_GET['bot']) || (trim($_GET['bot']) == '') ||
	!isset($_GET['file']) || (trim($_GET['file']) == '') ||
	!isset($_GET['folder']) || ($_GET['folder'] != 'read' && $_GET['folder'] != 'write')
	) {
		die('Incorrect params!');
	}

// escape bot name parameter
$botName = mysql_real_escape_string(urldecode($_GET['bot']),$GLOBALS['mysqlConnection']);

// sanitize the filename
function sanitizeFilename ($str)
{
    $str = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $str);
	$str = mb_ereg_replace("([\.]{2,})", '', $str);
	return $str;
}
$fileName = sanitizeFilename($_GET['file']);

function serveFile($file) {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($file).'"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	ob_clean();
	flush();
	readfile($file);
	exit;
}

// get bot ID
$res = mysql_query("SELECT id FROM fos_user WHERE full_name='$botName' AND email_confirmed='1' LIMIT 1;");
while ($line = mysql_fetch_assoc($res)) {
	
	// check if the user is logged in
	if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] != $line['id'])) {
		die('You are not logged in!');
	}
	
	// prepare the path fo tile
	if (isset($_GET['folder']) && $_GET['folder'] == 'read') {
		$path = $GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"].'/'.$line['id'].'/read/'.$fileName;
	} else{
		$path = $GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"].'/'.$line['id'].'/write/'.$fileName;
	}
	// serve the file if it exists
	if (file_exists($path) && !is_dir($path)) {
			serveFile($path);
		} else {
			die('File not accessible!');
	}
	
}



?>
