<?php
//===============================================
// DB connection & other settings
//===============================================
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/users/lib/functions/functions.php';

// escape bot name parameter
$botName = mysql_real_escape_string(urldecode($_GET['bot']),$GLOBALS['mysqlConnection']);

function serveFile($file, $filename = '') {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.($filename == '' ? basename($file) : $filename).'"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	ob_clean();
	flush();
	readfile($file);
}

// get bot ID
$res = mysql_query("SELECT bot_path,full_name FROM fos_user WHERE full_name='$botName' AND email_confirmed='1' LIMIT 1;");
while ($line = mysql_fetch_assoc($res)) {
	$file = $line['bot_path'];
	$name = $line['full_name'];

	// serve the bwapi.dll file
	if (isset($_GET['bwapi_dll']) && $_GET['bwapi_dll'] == 'true') {
		if (file_exists(dirname($file).'/BWAPI.dll')) {
			serveFile(dirname($file).'/BWAPI.dll');
		}
		exit;
	}

	// serve the bot binary file
	if (file_exists($file)) {
		$dir = dirname($file);
		if (!file_exists($dir.'.zip')) {
			if (file_exists($dir.'.tempDownload.zip')) {
				unlink($dir.'.tempDownload.zip');
			}
			// create the bot binary ZIP file if it doesn't exist
			requireBotBinaryZipFile($dir,$dir.'.tempDownload.zip');
			// check whether a different request already finished creating it since we last checked
			if (!file_exists($dir.'.zip')) {
				rename($dir.'.tempDownload.zip',$dir.'.zip');
			} else {
				// clean up (we will serve the other file instead)
				unlink($dir.'.tempDownload.zip');
			}
		}
		serveFile($dir.'.zip',$name.'.zip');
	} else {
		exit;
	}
}



?>
