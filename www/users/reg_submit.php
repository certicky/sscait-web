<?php

require_once('lib/connections/db.php');
include('lib/functions/functions.php');

	//For registration
	// we check if everything is filled in and perform checks
	if(empty($GLOBALS['loggingInEnabled']) || !$_POST['username'])
	{
		die(msg(0,"<p>Please enter your email.</p>"));
	}

	elseif(validateEmail($_POST['username']))
	{
		die(msg(0,"<p>Email address invalid.</p>"));
	}

	elseif(uniqueUser($_POST['username']))
	{
		die(msg(0,"Email already taken."));
	}

	if(!$_POST['name'])
	{
		die(msg(0,"<p>Please enter your full name.</p>"));
	}

	if(!nameCheck($_POST['name']))
	{
		die(msg(0,"<p>Forbidden characters in full name. Please use only alphanumeric characters and spaces.</p>"));
	}

	elseif(strlen($_POST['name'])<5) {
		die(msg(0,"<p>Full name must be at least 5 characters long.</p>"));
	}

	elseif(uniqueName($_POST['name']))
	{
		die(msg(0,"Bot name already taken."));
	}

	elseif(!$_POST['password'])
	{
		die(msg(0,"<p>Please enter a password.</p>"));
	}

	elseif(strlen($_POST['password'])<5)
	{
		die(msg(0,"<p>Passwords must be at least 5 characters.</p>"));
	}

	elseif($_POST['student'] != '0' && $_POST['student'] != '1')
	{
		die(msg(0,"<p>Are you a student? Value missing.</p>"));
	}

	if(!nameCheck($_POST['school']))
	{
		die(msg(0,"<p>Forbidden characters in school name. Please use only alphanumeric characters and spaces.</p>"));
	}

	// BOT INFO
	elseif(strlen($_POST['description'])>255)
	{
		die(msg(0,"<p>Description must have at least 255 characters.</p>"));
	}

	elseif($_POST['race'] != 'Terran' && $_POST['race'] != 'Zerg' && $_POST['race'] != 'Protoss' && $_POST['race'] != 'Random')
	{
		die(msg(0,"<p>Please select your bot's race.</p>"));
	}

	elseif($_POST['bot_type'] != 'JAVA_MIRROR' && $_POST['bot_type'] != 'JAVA_JNI' && $_POST['bot_type'] != 'AI_MODULE' && $_POST['bot_type'] != 'EXE')
	{
		die(msg(0,"<p>Invalid bot type.</p>"));
	}

	// CUSTOM FLAGS
	elseif(isset($_POST['flags']) && strlen($_POST['flags'])>255)
	{
		die(msg(0,"<p>Custom flags string too long.</p>"));
	}


	// BOT FILES
	// binary
	elseif(
		!isset($_FILES['bot_binary']) || // is the file there?
		$_FILES['bot_binary']['error'] > 0 || // was there any error?
		!fileIsZip($_FILES['bot_binary']['tmp_name'],$_FILES['bot_binary']['name'])) // is it ZIP?
	{
		if (!isset($_FILES['bot_binary'])) die(msg(0,"<p>Missing ZIP file with compiled bot BINARY.</p>".var_export($_FILES)));
		if ($_FILES['bot_binary']['error'] > 0) die(msg(0,"<p>Error uploading bot BINARY file: ".fileErrorCodeToMessage($_FILES['bot_binary']['error'])."<br/>Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a> and we'll help you upload your bot.</p>"));
		die(msg(0,"<p>Incorrect file type: ".$_FILES['bot_binary']['name']."<br/>It needs to be a ZIP file.</p>"));
	}
	// sources
	elseif(
		!isset($_FILES['bot_sources']) || // is the file there?
		$_FILES['bot_sources']['error'] > 0 || // was there any error?
		!fileIsZip($_FILES['bot_sources']['tmp_name'],$_FILES['bot_sources']['name'])) // is it ZIP?
	{
		if (!isset($_FILES['bot_sources'])) die(msg(0,"<p>Missing ZIP file with compiled bot SOURCES.</p>"));
		if ($_FILES['bot_sources']['error'] > 0) die(msg(0,"<p>Error uploading bot SOURCES file: ".fileErrorCodeToMessage($_FILES['bot_sources']['error'])."<br/>Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a> and we'll help you upload your bot.</p>"));
		die(msg(0,"<p>Incorrect file type: ".$_FILES['bot_sources']['name']."<br/>It needs to be a ZIP file.</p>"));
	}
	// additional
	elseif(
		(isset($_FILES['bot_additional_files']) && (trim($_FILES['bot_additional_files']['name']) != '')) && // if it's even there
		($_FILES['bot_additional_files']['error'] > 0 || // was there any error?
		!fileIsZip($_FILES['bot_additional_files']['tmp_name'],$_FILES['bot_additional_files']['name']))) // is it ZIP?
	{
		if ($_FILES['bot_additional_files']['error'] > 0 && $_FILES['bot_additional_files']['error'] != UPLOAD_ERR_NO_FILE) die(msg(0,"<p>Error uploading additional files: ".fileErrorCodeToMessage($_FILES['bot_additional_files']['error'])."<br/>Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a> and we'll help you upload your bot.</p>"));
		die(msg(0,"<p>Incorrect file type for archive containing additional files: ".$_FILES['bot_additional_files']['name']."<br/>It needs to be a ZIP file.</p>"));
	}



	else
		{
			// links to temporary filenames of uploaded zip files
			$zipBinary = $_FILES['bot_binary']['tmp_name'];
			$zipSources = $_FILES['bot_sources']['tmp_name'];
			if (isset($_FILES['bot_additional_files'])) {
				$zipAdditionalFiles = $_FILES['bot_additional_files']['tmp_name'];
			} else {
				$zipAdditionalFiles = '';
			}

			// add user
			$res = addUser($_POST['username'],$_POST['password'],$_POST['name'],$_POST['race'],$_POST['student'],$_POST['school'],$_POST['description'],$_POST['bot_type'],$zipBinary,$zipSources,$zipAdditionalFiles,$_POST['flags']);
				if ($res == 1){
					die(msg(0,"Failed to send activation email. Please Try registering again or contact the admin."));
				}
				if ($res == 2){
					die(msg(0,"There was an error registering your details. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a>."));
				}
				if ($res == 3){
					die(msg(0,"There was an error extracting your ZIP archives. Please <a href='".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/index.php?action=contact'>contact us</a>."));
				}
				if ($res == 4){
					if ($_POST['bot_type'] == "JAVA_JNI" || $_POST['bot_type'] == "JAVA_MIRROR") {
						die(msg(0,"Could not find a JAR file in your ZIP archive ".$_FILES['bot_binary']['name']."."));
					} else if ($_POST['bot_type'] == "EXE") {
						die(msg(0,"Could not find an EXE file in your ZIP archive ".$_FILES['bot_binary']['name']."."));
					} else {
						die(msg(0,"Could not find a DLL bot file in your ZIP archive ".$_FILES['bot_binary']['name']."."));
					}
				}
				if ($res == 5){
					die(msg(0,"BWAPI.dll was not found in your bot ZIP archive ".$_FILES['bot_binary']['name'].". Please add it - it should be in your 'Chaoslauncher' folder."));
				}
				if ($res == 99){
					die(msg(1,"<p>Registration successful! <a href='login.php'>Click here</a> to login.</p>"));
				}
		}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
