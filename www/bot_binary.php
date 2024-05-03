<?php
//===============================================
// DB connection & other settings
//===============================================
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

// extended class ZipArchive that can recursively add folders
class ZipArchivePlus extends ZipArchive
{
    public function addDir($location, $name)
    {
        $this->addEmptyDir($name);
        $this->addDirDo($location, $name);
    }
    private function addDirDo($location, $name)
    {
        $name .= '/';
        $location .= '/';
        $dir = opendir ($location);
        while ($file = readdir($dir))
        {
            if ($file == '.' || $file == '..') continue;
            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    }
}


// escape bot name parameter
$botName = mysql_real_escape_string(urldecode($_GET['bot']),$GLOBALS['mysqlConnection']);

function serveFile($file, $suffix = '') {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($file).$suffix.'"');
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

		// Prepare the ZIP File
		$tmpfile = tempnam("tmp",str_replace(" ","_",$name)."_");
		$zip = new ZipArchivePlus();
		$zip->open($tmpfile, ZipArchive::CREATE);

		// Stuff with content (all the files from bwapi-data/AI folder, minus bwapi.dll)
		if ($handle = opendir(dirname($file))) {
			while (false !== ($fileInFolder = readdir($handle))) {
				if ('.' === $fileInFolder) continue;
				if ('..' === $fileInFolder) continue;
				if ('bwapi.dll' === strtolower($fileInFolder)) continue;


				// add the file to the archive
				if (is_dir(dirname($file).'/'.$fileInFolder)) {
				    $zip->addDir(dirname($file).'/'.$fileInFolder, $fileInFolder);
				} else {
				    $zip->addFile(dirname($file).'/'.$fileInFolder, $fileInFolder);
				}
			}
			closedir($handle);
		}

		// Close the archive and send to users
		$zip->close();
		serveFile($tmpfile, '.zip');

		// Delete temporary ZIP archive
		unlink($tmpfile);

	} else {
		exit;
	}
}



?>
