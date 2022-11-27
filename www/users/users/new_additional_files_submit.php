<?php
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');
	
//For registration

	// BOT FILES
	if(
		!isset($_FILES['bot_additional_files']) || // is the file there?
		$_FILES['bot_additional_files']['error'] > 0 || // was there any error?
		!fileIsZip($_FILES['bot_additional_files']['tmp_name'],$_FILES['bot_additional_files']['name'])) // is it ZIP?
	{
		if (!isset($_FILES['bot_additional_files'])) die(msg(0,"<p>Missing ZIP file with read folder files.</p>"));
		if ($_FILES['bot_additional_files']['error'] > 0) die(msg(0,"<p>Error uploading additional files ZIP: ".fileErrorCodeToMessage($_FILES['bot_additional_files']['error'])."<br/>Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a>.</p>"));
		die(msg(0,"<p>Incorrect file type: ".$_FILES['bot_additional_files']['name']."<br/>It needs to be a ZIP file.</p>"));
	}

	else
		{
			// links to temporary filenames of uploaded zip files
			$zipAdditionalFiles = $_FILES['bot_additional_files']['tmp_name'];
			
			session_start();
			$getuser = getUserRecords($_SESSION['user_id']);
			$usr = $getuser[0];
			
			// upload new binary
			$res = uploadNewAdditionalFiles($usr,$zipAdditionalFiles);
				if ($res == 3){
					die(msg(0,"<p>There was an error extracting your ZIP archive. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a>.</p>"));
				}
				if ($res == 99){
					die(msg(1,"<p>New additional files successfully uploaded!</p>"));
				}
		}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
