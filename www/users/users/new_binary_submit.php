<?php
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

//For registration

	// BOT FILES
	// binary
	if(
		!isset($_FILES['bot_binary']) || // is the file there?
		$_FILES['bot_binary']['error'] > 0 || // was there any error?
		!fileIsZip($_FILES['bot_binary']['tmp_name'],$_FILES['bot_binary']['name'])) // is it ZIP?
	{
		if (!isset($_FILES['bot_binary'])) die(msg(0,"<p>Missing ZIP file with compiled bot BINARY.</p>".var_export($_FILES)));
		if ($_FILES['bot_binary']['error'] > 0) die(msg(0,"<p>Error uploading bot BINARY file: ".fileErrorCodeToMessage($_FILES['bot_binary']['error'])."<br/>Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a> and we'll help you upload your bot.</p>"));
		die(msg(0,"<p>Incorrect file type: ".$_FILES['bot_binary']['name']."<br/>It needs to be a ZIP file.</p>"));
	}

	else
		{
			// links to temporary filenames of uploaded zip files
			$zipBinary = $_FILES['bot_binary']['tmp_name'];
			
			session_start();
			$getuser = getUserRecords($_SESSION['user_id']);
			$usr = $getuser[0];
			
			// upload new binary
			$res = uploadNewBinary($usr,$zipBinary);
				if ($res == 3){
					die(msg(0,"There was an error extracting your ZIP archive. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a>."));
				}
				if ($res == 4){
					if ($usr['bot_type'] == "JAVA_JNI" || $usr['bot_type'] == "JAVA_MIRROR") {
						die(msg(0,"Could not find a JAR file in your ZIP archive ".$_FILES['bot_binary']['name'].". Bot was disabled. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact\'>contact us</a>."));
					} else if ($usr['bot_type'] == "EXE") {
						die(msg(0,"Could not find an EXE file in your ZIP archive ".$_FILES['bot_binary']['name'].". Bot was disabled. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact\'>contact us</a>."));
					} else {
						die(msg(0,"Could not find a DLL file in your ZIP archive ".$_FILES['bot_binary']['name'].". Bot was disabled. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact\'>contact us</a>."));
					}
				}
				if ($res == 5){
					die(msg(0,"BWAPI.dll was not found in your bot ZIP archive ".$_FILES['bot_binary']['name']." (it should be in your 'Chaoslauncher' folder). The bot needed to be disabled without it!"));
				}
				if ($res == 99){
					die(msg(1,"<p>New version of the bot successfully uploaded!</p>"));
				}
		}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
