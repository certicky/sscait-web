<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

$allowNameUpdates = false;

//----------Check if magic qoutes is on then stripslashes if needed----------
function secureInput($var)
{
	$output = '';
    if (is_array($var)){
        foreach($var as $key=>$val){
            $output[$key] = secureInput($val);
        }
    } else {
		$var = strip_tags(trim($var));
		if (function_exists("get_magic_quotes_gpc")) {
			$output = mysql_real_escape_string(get_magic_quotes_gpc() ? stripslashes($var) : $var);
		} else {
			$output = mysql_real_escape_string($var);
		}
	}
	return $output;
}

//----------Function for Logging in users----------
function login($user,$pass)
{
	$user = secureInput($user);
	$pass = secureInput($pass);

	//$salt = 's+(_a*';
	$pass = md5($pass/*.$salt*/);
	//$lastLogin = date("l, M j, Y, g:i a");

		//Use the input username and password and check against 'users' table
		$query = mysql_query('SELECT id,password_hash,temporary_password_hash,email_confirmed FROM fos_user WHERE email = "'.secureInput($user).'" AND (password_hash = "'.secureInput($pass).'" OR temporary_password_hash = "'.secureInput($pass).'")') or die (mysql_error());

		if(mysql_num_rows($query) == 1)
		{

			$row = mysql_fetch_assoc($query);
			// if this login is done with temp passwd, make it the main one
			if ($row['temporary_password_hash'] == secureInput($pass)) {
				mysql_query("UPDATE fos_user SET password_hash='".secureInput($pass)."',temporary_password_hash='',last_update_time=NOW() WHERE id='".$row['id']."';") or die(mysql_error());
			}

			if ($row['email_confirmed'] != '1') {
				return 3;
			} else {
				set_login_sessions( $row['id'], $row['password_hash'] ? TRUE : FALSE );
				return 99;
			}

			/*
			$update = mysql_query('UPDATE users SET last_login = "'.$lastLogin.'" WHERE id = "'.$row['id'].'"');
			if ($row['active'] == 1 ) {
				set_login_sessions ( $row['id'], $row['password'] ? TRUE : FALSE );
					if ($row['level_access'] == 2) {
						return 99;
						}
			}
			if ($row['active'] == 2) {return 2;}
			if ($row['active'] == 0) {return 3;}
			* */
		} else return 1;
}

//----------Function for logging off users----------
function logoff()
{
  //session must be started before anything
		session_start ();

		//if we have a valid session
		if ( $_SESSION['logged_in'] == TRUE )
		{
			//unset the sessions (all of them - array given)
			unset ( $_SESSION );
			//destroy what's left
			session_destroy ();
		}

		//It is safest to set the cookies with a date that has already expired.
		if ( isset ( $_COOKIE['cookie_id'] ) && isset ( $_COOKIE['authenticate'] ) ) {
			/**
			 * uncomment the following line if you wish to remove all cookies
			 * (don't forget to comment ore delete the following 2 lines if you decide to use clear_cookies)
			 */
			//clear_cookies ();
			setcookie ( "cookie_id", '', time() - 3600);
			setcookie ( "authenticate", '', time() - 3600 );
		}

		//redirect the user to the default "logout" page
  header("Location: log_off.php");
}

//*******************************site access**************************
//----------Set Login Sessions----------

function set_login_sessions ( $user_id, $password )
{
		//start the session
		session_start();

		//set the sessions
		$_SESSION['user_id'] = $user_id;
		$_SESSION['logged_in'] = TRUE;
}

//----------checks the level access of users----------
function checkLogin ( $levels )
{
		session_start ();
		$kt = explode ( ' ', $levels );

		if ( !isset($_SESSION['logged_in']) ) {

			$access = FALSE;

			/*
			if ( isset ( $_COOKIE['cookie_id'] ) ) {//if we have a cookie

			$query = mysql_query('SELECT * FROM fos_user WHERE id = "'.mysql_real_escape_string($_COOKIE['cookie_id']).'"');

			if(mysql_num_rows($query) == 1)
				$row = mysql_fetch_assoc($query);
			echo 'were here';

			if ( $_COOKIE['authenticate'] == md5 ( getIP () . $row['password'] . $_SERVER['USER_AGENT'] ) ) {
						//we set the sessions so we don't repeat this step over and over again
						$_SESSION['user_id'] = $row['id'];
						$_SESSION['logged_in'] = TRUE;


						//now we check the level access, we might not have the permission
						//if ( in_array ( get_level_access ( $_SESSION['user_id'] ), $kt ) ) {
						//	//we do?! horray!
						//	$access = TRUE;
						//}

						$access = TRUE;
					}
				}*/
		}
		else {
			/*
			$access = FALSE;
			if ( in_array ( get_level_access ( $_SESSION['user_id'] ), $kt ) ) {
				$access = TRUE;
			}
			*/
			$access = TRUE;
		}

		if ( $access == FALSE ) {
			header("Location: ../login.php");
		}
}

//----------Get Level Access----------

function get_level_access ( $user_id )
{
	return 99;

		$query = mysql_query("SELECT `level_access` FROM `users` WHERE `id` = '" . 	mysql_real_escape_string ( $user_id ) . "'");
		if ( mysql_num_rows ( $query ) == 1 )
		{
			$row = mysql_fetch_array ( $query );
		}
		return $row['level_access'];

}

//----------IP functions----------
function ip_first ( $ips )
{
		if ( ( $pos = strpos ( $ips, ',' ) ) != false ) {
			return substr ( $ips, 0, $pos );
		}
		else {
			return $ips;
		}
}

//----------ip_valid - will try to determine if a given ip is valid or not-----------

function ip_valid ( $ips )
{
	if ( isset( $ips ) ) {
		$ip    = ip_first ( $ips );
		$ipnum = ip2long ( $ip );
		if ( $ipnum !== -1 && $ipnum !== false && ( long2ip ( $ipnum ) === $ip ) ) {
			if ( ( $ipnum < 167772160   || $ipnum > 184549375 ) && // Not in 10.0.0.0/8
			( $ipnum < - 1408237568 || $ipnum > - 1407188993 ) && // Not in 172.16.0.0/12
			( $ipnum < - 1062731776 || $ipnum > - 1062666241 ) )   // Not in 192.168.0.0/16
			return true;
		}
	}
	return false;
}

//----------getIP - returns the IP of the visitor----------
function getIP ()
{
	$check = array(
			'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED', 'HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM',
			'HTTP_CLIENT_IP'
			);

	foreach ( $check as $c ) {
		if ( ip_valid ( $_SERVER [ $c ] ) ) {
			return ip_first ( $_SERVER [ $c ] );
		}
	}

	return $_SERVER['REMOTE_ADDR'];
}

//----------Random string generation function----------

function random_string($type = 'alnum', $len = 5)
{
	switch($type)
	{
		case 'alnum'	:
		case 'numeric'	:
		case 'nozero'	:

				switch ($type)
				{
					case 'alnum'	:	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric'	:	$pool = '0123456789';
						break;
					case 'nozero'	:	$pool = '123456789';
						break;
				}

				$str = '';
				for ($i=0; $i < $len; $i++)
				{
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				return $str;
		  break;
		case 'unique' : return md5(uniqid(mt_rand()));
		  break;
	}
}

//*******************************unique**************************
//---------checks if record stored in db already exists or not--------

function uniqueUser($user)
{
	$user=secureInput($user);
	$sql = "SELECT email FROM fos_user WHERE email = '" . $user ."' ";
	$res = mysql_query($sql);
	$num = mysql_num_rows($res);

	if ($num > 0)
		return true;
	return false;
}

function uniqueName($name)
{
	$name = secureInput($name);
	$sql = "SELECT email FROM fos_user WHERE full_name = '" . $name ."' ";
	$res = mysql_query($sql);
	$num = mysql_num_rows($res);

	if ($num > 0)
		return true;
	return false;
}

function uniqueEmail($email)
{
	$email=secureInput($email);
	$sql = "SELECT COUNT(*) as NUMBER FROM users WHERE email = '" . $email ."' ";
	$res = mysql_query($sql);
	$num = mysql_result($res,0,"NUMBER");

	if ($num > 0)
		return true;
	return false;
}

//----------Function for checking existence of users----------
function checkUserInfo($id)
{
	$id = secureInput($id);

	$sql = "SELECT id FROM users WHERE id='".$id."'";
	$res = mysql_query($sql);
	$rows = mysql_num_rows($res);

	if($rows == 0) return TRUE;
		return FALSE;
}

//*******************************Function for validating an email address**************************

function validateEmail($email)
{
	$email=secureInput($email);
   return ( ! preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $email)) ? TRUE : FALSE;
}


//-----Function for Validating a given string against numeric characters----------

function validateNumeric($str)
{
	$str=secureInput($str);
	return ( ! preg_match("/^[0-9\.]+$/", $str)) ? FALSE : TRUE;
}

//----------Function for extracting ZIP archives----------
function extractZip($zipFile,$destination) {
	if (trim($zipFile) == '') return TRUE;
	$zip = new ZipArchive;
	if ($zip->open($zipFile) === TRUE) {
		$zip->extractTo($destination);
		$zip->close();
		return TRUE;
	} else {
		return FALSE;
	}
}

//----------Function that finds a JAR/DLL file in the folder and returns it's name----------
function getFileByExtension($folder,$extension) {
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			$strings = explode('.', $entry);
			$ext = strtolower(end($strings));
			if ($ext==strtolower($extension) && ($entry != "BWAPI.dll")) {
				return $entry;
				break;
			}
		}
		closedir($handle);
	}
	return FALSE;
}

//----------Function that checks if BWAPI.dll is in the folder----------
function isBWAPIdllInFolder($folder) {
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry == "BWAPI.dll") {
				return TRUE;
			}
		}
		closedir($handle);
	}
	return FALSE;
}

//----------Function for setting permissions to newly created folders/files----------
function chmod_R($path, $filemode, $dirmode) {
    if (is_dir($path) ) {
        if (!chmod($path, $dirmode)) {
            $dirmode_str=decoct($dirmode);
            print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
            print "  `-> the directory '$path' will be skipped from recursive chmod\n";
            return;
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if($file != '.' && $file != '..') {  // skip self and parent pointing directories
                $fullpath = $path.'/'.$file;
                chmod_R($fullpath, $filemode,$dirmode);
            }
        }
        closedir($dh);
    } else {
        if (is_link($path)) {
            print "link '$path' is skipped\n";
            return;
        }
        if (!chmod($path, $filemode)) {
            $filemode_str=decoct($filemode);
            print "Failed applying filemode '$filemode_str' on file '$path'\n";
            return;
        }
    }
}

//----------Function for adding user's profile----------
function addUser($user,$pass,$name,$race,$student,$school,$description,$botType,$zipBinary,$zipSources,$zipAdditionalFiles,$flags)
{
	$user = secureInput($user);
	$pass = secureInput($pass);
	$name = secureInput($name);
	$race = secureInput($race);
	$student = secureInput($student);
	//$site_url = secureInput($site_url);
	$school = secureInput($school);
	$description = secureInput($description);
	$botType = secureInput($botType);
	$customFlags = secureInput($flags);

	//Encrypt password for database
	//$salt = 's+(_a*';
	$pass = md5($pass/*.$salt*/);

	$rand_str = random_string('alnum', 8);
	$activation_key = md5($rand_str/*.$salt*/);

	$reg_date = date("l, M j, Y, g:i a");

	$sql = "INSERT INTO fos_user (email,password_hash,bot_enabled,full_name,bot_race,student,school,bot_description,last_update_time,confirmation_token,bot_type,custom_flags) VALUES ('".$user."','".$pass."',1,'".$name."','".$race."','".$student."','".$school."','".$description."',NOW(),'".$activation_key."','".$botType."','".$customFlags."')";
	$res = mysql_query($sql) or die(mysql_error());
	if($res){

		// try extracting the zip files
		$idRes = mysql_fetch_assoc(mysql_query("SELECT id FROM fos_user WHERE email='$user';")) or die(mysql_error());
		$id = $idRes['id'];
		$dest = $GLOBALS['BOTS_FOLDER_WITHOUT_SLASH'].'/'.$id;
		// create necessary directories (read, write...)
		umask(0);
		if (!file_exists($dest.'/write')) {
			mkdir($dest.'/write/',0777,true);
			chmod_R( $dest.'/write', 0777, 0777);
		}
		if (!file_exists($dest.'/read')) {
			mkdir($dest.'/read/',0777,true);
			chmod_R( $dest.'/read', 0777, 0777);
		}
		if (!file_exists($dest.'/AI')) {
			mkdir($dest.'/AI/',0777,true);
			chmod_R( $dest.'/AI', 0777, 0777);
		}
		if (!extractZip($zipBinary,$dest.'/AI') || !extractZip($zipAdditionalFiles,$dest.'/read') || !copy($zipSources,$dest.'/sources.zip')) {
			$sql = "DELETE FROM fos_user WHERE email='".$user."'";
			$res = mysql_query($sql) or die(mysql_error());
			return 3;
		}
		chmod_R( $dest.'/AI', 0777, 0777);  chmod_R( $dest.'/read', 0777, 0777);  chmod_R( $dest.'/write', 0777, 0777);

		// get the filename of bot binary, check for BWAPI.dll and put that filename to DB
		if ($botType == 'JAVA_JNI' || $botType == 'JAVA_MIRROR') {
			$botBinaryFileName = getFileByExtension($dest.'/AI','jar');
		} else if ($botType == 'EXE') {
			$botBinaryFileName = getFileByExtension($dest.'/AI','exe');
		} else {
			$botBinaryFileName = getFileByExtension($dest.'/AI','dll');
		}
		if ($botBinaryFileName == FALSE) {
			$sql = "DELETE FROM fos_user WHERE email='".$user."'";
			$res = mysql_query($sql) or die(mysql_error());
			return 4;
		} else if (!isBWAPIdllInFolder($dest.'/AI')) {
			$sql = "DELETE FROM fos_user WHERE email='".$user."'";
			$res = mysql_query($sql) or die(mysql_error());
			return 5;
		} else {
			$sql = "UPDATE fos_user SET bot_path='".$dest.'/AI/'.$botBinaryFileName."' WHERE email='".$user."'";
			$res = mysql_query($sql);
		}

		// create run_proxy.bat for JAVA bots
		/* DISABLED, because client does this
		if ($botType == 'JAVA_JNI' || $botType == 'JAVA_MIRROR') {
			file_put_contents($dest.'/AI/run_proxy.bat',"java -jar ".$botBinaryFileName."\n");
		}
		*/

		//build email to be sent
		$to = $user;
		$subject = "Activate Your Account";

		$title = "Welcome to [SSCAI] Tournament!";
		$message = "
		<h3>Account Activation</h3>
		<p>Dear user, thank you for registering to SSCAI Tournament.</p>
		<p>Your account has been created. You must now confirm your e-mail address at the following link:<br>".$GLOBALS["DOMAIN_WITHOUT_SLASH"]."/users/confirm_user_reg.php?activation_key=".$activation_key." <br><br>Please do not reply to this message.</p>
		<p>Sincerely,</p>
		<p>The [SSCAI] Team.</p>
		";

		// SUCCESS (99)
		require_once 'lib/connections/email.php';
		if (sendMailFromGmail($to,$subject,$message,$title)) {
			return 99;
		} else {
			$sql = "DELETE FROM fos_user WHERE email='".$user."'";
			$res = mysql_query($sql) or die(mysql_error());
			return 1;
		}
	}
		else return 2;

}

//-----------Recursively deletes a folder and all its contents---
function deleteFolder($path)
{
    if (is_dir($path) === true)
    {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file)
        {
            deleteFolder(realpath($path) . '/' . $file);
        }
        return rmdir($path);
    }
    else if (is_file($path) === true)
    {
        return unlink($path);
    }
    return false;
}
//----------Uploads a new bot binary----------
function uploadNewBinary($usr,$zipBinary)
{

		// try extracting the zip files
		$id = $usr['id'];
		$dest = $GLOBALS['BOTS_FOLDER_WITHOUT_SLASH'].'/'.$id;
		if (!extractZip($zipBinary,$dest.'/AI-temp')) {
			return 3;
		}
		chmod_R( $dest.'/AI-temp', 0777, 0777);
		deleteFolder($dest.'/AI');
		rename($dest.'/AI-temp',$dest.'/AI');

		// get the filename of bot binary, check for BWAPI.dll and put it to DB
		if ($usr['bot_type'] == 'JAVA_JNI' || $usr['bot_type'] == 'JAVA_MIRROR') {
			$botBinaryFileName = getFileByExtension($dest.'/AI','jar');
		} else if ($usr['bot_type'] == 'EXE') {
			$botBinaryFileName = getFileByExtension($dest.'/AI','exe');
		} else {
			$botBinaryFileName = getFileByExtension($dest.'/AI','dll');
		}

		if ($botBinaryFileName == FALSE) {
			$sql = "UPDATE fos_user SET bot_enabled='0',bot_path='',last_update_time=NOW()  WHERE id='".$id."';";
			$res = mysql_query($sql) or die(mysql_error());
			return 4;
		} else if (!isBWAPIdllInFolder($dest.'/AI')) {
			$sql = "UPDATE fos_user SET bot_enabled='0',bot_path='',last_update_time=NOW()  WHERE id='".$id."';";
			$res = mysql_query($sql) or die(mysql_error());
			return 5;
		} else {
			$sql = "UPDATE fos_user SET bot_path='".$dest.'/AI/'.$botBinaryFileName."',last_update_time=NOW(), bot_enabled='1' WHERE id='".$id."';";
			$res = mysql_query($sql);
		}


		// create run_proxy.bat for JAVA bots
		/* DISABLED, because client creates this if needed
		if ($usr['bot_type'] == 'JAVA_JNI' || $usr['bot_type'] == 'JAVA_MIRROR') {
			file_put_contents($dest.'/AI/run_proxy.bat',"java -jar ".$botBinaryFileName."\n");
		}
		*/

		return 99;
}

//----------Uploads new additional files----------
function uploadNewAdditionalFiles($usr,$zipAdditionalFiles)
{
		// try extracting the zip files
		$id = $usr['id'];
		$dest = $GLOBALS['BOTS_FOLDER_WITHOUT_SLASH'].'/'.$id;
		if (!extractZip($zipAdditionalFiles,$dest.'/read-temp')) {
			return 3;
		}

		chmod_R( $dest.'/read-temp', 0777, 0777);
		deleteFolder($dest.'/read');
		rename($dest.'/read-temp',$dest.'/read');

        // clears write/ too
        $writeDirPath = $dest . '/write';
        deleteFolder($writeDirPath);
        mkdir($writeDirPath, 0777, true);
        chmod_R($writeDirPath, 0777, 0777);

		$sql = "UPDATE fos_user SET last_update_time=NOW() WHERE id='".$id."';";
		$res = mysql_query($sql);

		return 99;
}

//----------Function for editing user's and admin's profile by admin----------
function editUser($id,$email,$firstname,$lastname,$dialing_code,$phone,$city,$country)
{
		    $id = secureInput($id);
		 $email = secureInput($email);
	$first_name = secureInput($firstname);
	 $last_name = secureInput($lastname);
  $dialing_code = secureInput($dialing_code);
		 $phone = secureInput($phone);
		  $city = secureInput($city);
	   $country = secureInput($country);


	if (!empty($email)){
			$sql = "UPDATE users SET email = '" . $email . "', first_name = '" . $first_name . "', last_name = '" . $last_name . "', dialing_code = '" . $dialing_code . "', phone = '" . $phone . "', city = '" . $city . "', country = '" . $country . "' WHERE id = '" . $id . "'";
			$res = mysql_query($sql) or die(mysql_error());
				if(!$res) return 4;
				return 99;
			}
	if(empty($email)){
			$sql = "UPDATE users SET first_name = '" . $first_name . "', last_name = '" . $last_name . "', dialing_code = '" . $dialing_code . "', phone = '" . $phone . "', city = '" . $city . "', country = '" . $country . "' WHERE id = '" . $id . "'";
			$res = mysql_query($sql) or die(mysql_error());
				if(!$res) return 4;
				return 99;
			}
}

//----------Function for changing password----------//
function updatePass($id,$opass,$pass)
{
	$id = secureInput($id);
	$opass = secureInput($opass);
	$pass = secureInput($pass);

	//$salt = 's+(_a*';
	$opasssalt = md5($opass/*.$salt*/);

	$query = mysql_query('SELECT `password_hash` FROM `fos_user` WHERE `id` = "'.$id.'"');
	$row = mysql_fetch_assoc($query);

	if ($opasssalt != $row['password_hash']){
		return 2;
	}else{

	//Encrypt password for database
	//$salt = 's+(_a*';
	$new_password = md5($pass/*.$salt*/);

	$sql = "UPDATE fos_user SET password_hash = '" . $new_password . "' WHERE id = '" . $id . "'";
 	$res = mysql_query($sql);
		if(!$res) return 3;
		return 99;
	}
}

//----------Function for admin change user passwords----------
function adminUpdatePass($uid,$pass)
{
	$uid = secureInput($uid);
	$pass = secureInput($pass);

	//Encrypt password for database
	//$salt = 's+(_a*';
	$new_password = md5($pass/*.$salt*/);

	$sql = "UPDATE users SET password = '" . $new_password . "' WHERE id = '" . $uid . "'";
 	$res = mysql_query($sql);
	if($res) return 99;
		return 1;
}

//----------Function for deleting users by admin----------
function deleteUser($id)
{
	$sql = "SELECT * FROM users WHERE id = '".$id."'";
	$res = mysql_query($sql);
	if ($res){
		$del = "DELETE FROM users WHERE id = '".$id."'";
		$result = mysql_query($del);
			if($result)
				return 99;
					return 1;
	} else return 2;
}

//----------Function for suspending users by admin----------
function suspendUser($id)
{
	$sql = "SELECT id,active FROM users WHERE id = '".$id."'";
	$res = mysql_query($sql);
	if ($res){
		$update = "UPDATE users SET active = 2 WHERE id = '".$id."'";
		$result = mysql_query($update);
			if ($result)
				return 99;
					return 1;
	} else return 2;
}

//----------Function for reactivating users by admin----------
function unsuspendUser($id)
{
	$sql = "SELECT id,active FROM users WHERE id = '".$id."'";
	$res = mysql_query($sql);
	if ($res){
		$update = "UPDATE users SET active = 1 WHERE id = '".$id."'";
		$result = mysql_query($update);
			if ($result)
				return 99;
					return 1;
	} else return 2;
}

//---------Checks if the "name" string is correct (alphanumeric+spaces)---
function nameCheck($string) {
    return preg_match("/^[a-zA-Z0-9,\s]*$/", $string);
}

//----------Function for getting user records----------
function getUserRecords($id)
{
	global $getuser;
	$sql = "SELECT * FROM fos_user WHERE id = '". $id . "'";
	$res = mysql_query($sql);

	$c=0;
	while ($a_row = mysql_fetch_array($res)) {
		$getuser[$c]["id"] = $a_row["id"];
		$getuser[$c]["full_name"] = $a_row["full_name"];
		$getuser[$c]["email"] = $a_row["email"];
		$getuser[$c]["bot_race"] = $a_row["bot_race"];
		$getuser[$c]["bot_description"] = $a_row["bot_description"];
		$getuser[$c]["student"] = $a_row["student"];
		$getuser[$c]["bot_type"] = $a_row["bot_type"];
		$getuser[$c]["school"] = $a_row["school"];
		$getuser[$c]["last_update_time"] = $a_row["last_update_time"];
		$getuser[$c]["bot_path"] = $a_row["bot_path"];
		$getuser[$c]["bot_enabled"] = $a_row["bot_enabled"];

	$c++;
    }
	return $getuser;
}

//*******************************insert form data **************************
//----------Function for Logging in admin----------
function adminLogin($user,$pass)
{
	$user = secureInput($user);
	$pass = secureInput($pass);

	//$salt = 's+(_a*';
	$pass = md5($pass/*.$salt*/);
	$lastLogin = date("l, M j, Y, g:i a");

		//Use the input username and password and check against 'users' table
		$query = mysql_query('SELECT id, username, password, active, level_access FROM users WHERE username = "'.secureInput($user).'" AND password = "'.secureInput($pass).'" AND level_access = 1') or die (mysql_error());

		if(mysql_num_rows($query) == 1)
		{
			$row = mysql_fetch_assoc($query);
			$update = mysql_query('UPDATE users SET last_login = "'.$lastLogin.'" WHERE id = "'.$row['id'].'"');
			if ($row['active'] == 1 ) {
				set_login_sessions ( $row['id'], $row['password'] ? TRUE : FALSE );
					if ($row['level_access'] == 1) {
						return 99;
						}
			} else return 1;
		} else return 2;
}

//----------Function for password recovery----------
function pass_recovery($email)
{
	$email = secureInput($email);

	$sql = "SELECT id,email,full_name FROM fos_user WHERE email = '".$email."'";
	$res = mysql_query($sql) or die(mysql_error());
	$num = mysql_num_rows($res);
	$row = mysql_fetch_assoc($res);

	if($num == 1)
		{
		$temp_password = random_string('alnum', 8);
		//$salt = 's+(_a*';
		$temp_pass = md5($temp_password/*.$salt*/);

		$update = mysql_query("UPDATE fos_user SET temporary_password_hash='".$temp_pass."' WHERE email='".$email."'") or die(mysql_error());

		if($update){
			//build email to be sent
			$to = $row['email'];
			$subject = "Password Reset Request";

			$message = "
				<html>
				  <head>
					<title>Password Reset</title>
				  </head>
				  <body>
					<h3>Your New Password</h3>
					<p>Dear ".$row['full_name'].", someone (presumably you), has requested a password reset.</p>
					<p>Your new temporary password is: ".$temp_password."</p>
					<p>If you use this temporary password to log in, the old one will stop working.
					If you did not initiate this request, simply disregard this email, and we're sorry for bothering you.</p>
					<br/><br/>
					<p>Sincerely,</p>
					<p>[SSCAIT] Team.</p>
				  </body>
				</html>
				";

			require_once 'lib/connections/email.php';
			if (sendMailFromGmail($to,'Password Reset',$message,'Password Reset')) {
				return 99;
			} else {
				return 1;
			}
		} else return 2;
	}
	else return 3;
}

//----------Function for confirming password----------
function confirm_pass($id,$new)
{
	 $id = secureInput($id);
	$new = secureInput($new);

	$query = mysql_query("SELECT password,temp_pass,temp_pass_active FROM users WHERE id = '".$id."'");

	if(mysql_num_rows($query)==1)
	{
		$row = mysql_fetch_assoc($query);
		if($row['temp_pass']==$_GET['new'] && $row['temp_pass_active']==1)
		{
			$update = mysql_query("UPDATE users SET temp_pass_active=0 WHERE id = '".mysql_real_escape_string($_GET['id'])."'");
			if($update){
				return 99;
			} else return 1;
		}
		else
		{
			return 2;
		}
	}
	else {
		return 3;
	}
}

//----------Function for confirming user through email submitted----------
function confirm_user_reg($activation_key)
{
	$activation_key = mysql_real_escape_string($activation_key);
	$query = mysql_query("SELECT id,email_confirmed,email,full_name,bot_type,bot_race FROM fos_user WHERE confirmation_token = '".$activation_key."'");

	if(mysql_num_rows($query)==1)
	{
		$row = mysql_fetch_assoc($query);
		$id = $row['id'];
		if($row['email_confirmed']==0)
		{
			$update = mysql_query("UPDATE fos_user SET email_confirmed=1,confirmation_token=NULL WHERE id = '".$id."'");
			// SUCCESS
			if($update){
				// send mail to admin
				$email = $row['email'];
				$name = $row['full_name'];
				$botType = $row['bot_type'];
				$race = $row['bot_race'];
				require_once 'lib/connections/email.php';
				sendMailFromGmail($GLOBALS['ADMIN_EMAIL'],"ADMIN: New user!","New user has just confirmed the registration.<br>ID: $id<br>Email: $email<br>Name: $name<br>Type: $botType<br>Race: $race<br><br>Please test and activate the bot (".$GLOBALS['DOMAIN_WITHOUT_SLASH']."/phpmyadmin/).","ADMIN: New user!");
				return 99;
			} else return 1;
		}
		if($row['email_confirmed']==1)
		{
			return 2;
		}
	}
	else {
		return 3;
	}
}

//----------Function for inserting info to contact us----------
function contactUs($name,$email,$message,$site_email)
{
	   $name = secureInput($name);
	  $email = secureInput($email);
    $message = secureInput($message);
 $site_email = secureInput($site_email);

		//build email to be sent
		$to = $site_email;
		$subject = "New message from ".$email;

		$message = "
			<html>
			  <head>
				<title>New message from".$name."</title>
			  </head>
			  <body>
				<h3>Query / Comment</h3>
				<p>Site Admin, ".$name." has sent a query/comment. It is as below:</p>
				<p>".$message."</p>
			  </body>
			</html>
			";

			// To send HTML mail, the Content-type header must be set
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

			$mail_send = mail($to, $subject, $message, $headers );
			if ($mail_send)
				return 99;
			 return 1;
}

//----------get Site Settings ----------
function getSiteSettings()
{
	global $sitesettings;

	$sql = "SELECT id,site_url,site_email FROM site_settings";
	$res = mysql_query($sql);

		$c=0;
		while ($row = mysql_fetch_array($res)) {
			$sitesettings[$c]["id"] = $row["id"];
			$sitesettings[$c]["site_url"] = $row["site_url"];
			$sitesettings[$c]["site_email"] = $row["site_email"];

		$c++;
		}
		return $sitesettings;
}

//----------Function for updating site settings----------
function updateSiteSet($site_url,$site_email)
{
		   $site_url = secureInput($site_url);
		 $site_email = secureInput($site_email);

		$sql = "SELECT * FROM site_settings";
		$res = mysql_query($sql);
		$numRows = mysql_num_rows($res);

		if ($numRows == 0){
			$sql = "INSERT INTO site_settings (site_url,site_email) VALUES('".$site_url."','".$site_email."')";
			$res = mysql_query($sql);
				if(!$res) return 1;
			return 99;
			}
		if ($numRows > 0){
			$sql = "UPDATE site_settings SET site_url = '" . $site_url . "', site_email = '" . $site_email . "' ";
			$res = mysql_query($sql);
				if(!$res) return 1;
			return 99;
			}
}

//----------get country via select option----------
function get_country()
{

	$sql = "SELECT id,country FROM countries ORDER BY id ASC";
	$res = mysql_query($sql);

	echo "<select id=\"country\" class=\"searchbox\" name=\"country\">";
		echo "<option value=\"\">Select One</option>";
		while ($row = mysql_fetch_assoc($res)){
			echo "<option value=\"".$row['country']."\">".$row['country']."</option>";
			}
	echo "</select>";
}

//----------get countries via select option from users----------
function get_select_countries($id)
{
	$id = secureInput($id);

	$sql = "SELECT country FROM countries ORDER BY country ASC";
	$res = mysql_query($sql);

	$sql1 = "SELECT country FROM users WHERE id='".$id."'";
	$res1 = mysql_query($sql1);
	$row1 = mysql_fetch_assoc($res1);

	if($row1){
		echo "<select name=\"country\">";
		echo "<option selected=".$row1['country']." value=\"".$row1['country']."\">".$row1['country']."</option>";
		while ($row = mysql_fetch_assoc($res)){
			echo "<option value=\"".$row['country']."\">".$row['country']."</option>";
			}
		echo "</select>";
		} else {
			echo "<select name=\"country\">";
			while ($row = mysql_fetch_assoc($res)){
				echo "<option value=\"".$row['country']."\">".$row['country']."</option>";
			}
			echo "</select>";
	}
}

//----------get dialing code via select option----------
function get_dialing_code($id)
{
	$id = secureInput($id);

	$sql = "SELECT dialing_code FROM dialing_code ORDER BY name ASC";
	$res = mysql_query($sql);

	$sql1 = "SELECT dialing_code FROM users WHERE id='".$id."'";
	$res1 = mysql_query($sql1);
	$row1 = mysql_fetch_assoc($res1);

	if($row1){
		echo "<select name=\"dialing_code\">";
		echo "<option selected=".$row1['dialing_code']." value=\"".$row1['dialing_code']."\">".$row1['dialing_code']."</option>";
		while ($row = mysql_fetch_assoc($res)){
			echo "<option value=\"".$row['dialing_code']."\">".$row['dialing_code']."</option>";
			}
		echo "</select>";
		} else {
			echo "<select name=\"dialing_code\">";
			while ($row = mysql_fetch_assoc($res)){
				echo "<option value=\"".$row['dialing_code']."\">".$row['dialing_code']."</option>";
			}
			echo "</select>";
	}
}

//////////////////////////////////////////User Image Functions//////////////////////////////////
//----------Check image size----------
function checkImageSize($tmpfile, $max)
{
	//check the tmpimage file size and see if it is to big returns true if to large
	$size = filesize($tmpfile);
	if ($size > $max)
		return true;
	return false;
}

//----------Check allowed extension----------
function checkAllowedExt($file)
{
	$temp = strtolower($file);
	$ext = pathinfo($temp, PATHINFO_EXTENSION);
	$allowed = array('gif', 'jpg', 'jpeg', 'png');
	if (!in_array($ext, $allowed))
		return true;
	return false;
}

//----------Open image file----------
function openImage($file)
{
	// Get extension and return it
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	switch(strtolower($ext)) {
		case 'jpg':
		case 'jpeg':
			$im = @imagecreatefromjpeg($file);
			break;
		case 'gif':
			$im = @imagecreatefromgif($file);
			break;
		case 'png':
			$im = @imagecreatefrompng($file);
			break;
		default:
			$im = false;
			break;
	}
	return $im;
}

//----------Create thumbnail image for user----------
function createThumb($file, $ext, $width)
{
	$im = '';
	$im = openImage($file);

	if (empty($im)) {
		return false;
	}

	$old_x = imagesx($im);
   	$old_y = imagesy($im);

    $new_w = (int)$width;

	if (($new_w <= 0) or ($new_w > $old_x)) {
		$new_w = $old_x;
   	}

   	$new_h = ($old_x * ($new_w / $old_x));

    if ($old_x > $old_y) {
        $thumb_w = $new_w;
        $thumb_h = $old_y * ($new_h / $old_x);
    }
    if ($old_x < $old_y) {
        $thumb_w = $old_x * ($new_w / $old_y);
        $thumb_h = $new_h;
    }
    if ($old_x == $old_y) {
		$thumb_w = $new_w;
		$thumb_h = $new_h;
    }

	$thumb = imagecreatetruecolor($thumb_w,$thumb_h);

	if ($ext == 'png') {
		imagealphablending($thumb, false);
		$colorTransparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
       	imagefill($thumb, 0, 0, $colorTransparent);
       	imagesavealpha($thumb, true);
	} elseif ($ext == 'gif') {
    	$trnprt_indx = imagecolortransparent($im);
        if ($trnprt_indx >= 0) {
        	//its transparent
           	$trnprt_color = imagecolorsforindex($im, $trnprt_indx);
           	$trnprt_indx = imagecolorallocate($thumb, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
           	imagefill($thumb, 0, 0, $trnprt_indx);
           	imagecolortransparent($thumb, $trnprt_indx);
		}
	}

	imagecopyresampled($thumb,$im,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);

	//choose which image program to use
	switch(strtolower($ext)) {
		case 'jpg':
		case 'jpeg':
			imagejpeg($thumb,$file);
			break;
		case 'gif':
			imagegif($thumb,$file);
			break;
		case 'png':
			imagepng($thumb,$file);
			break;
		default:
			return false;
			break;
	}

	imagedestroy($im);
    imagedestroy($thumb);
}

//----------Move uploaded image file ----------
function moveUploadImage($path, $file, $tmpfile, $max)
{
	//upload your image and give it a random name so no conflicts occur
	$rand = rand(1000,9000);
	$save_path = $path . $rand . $file;

	//prep file for db and gd manipulation
	$bad_char_arr = array(' ', '&', '(', ')', '*', '[', ']', '<', '>', '{', '}');
	$replace_char_arr = array('-', '_', '', '', '', '', '', '', '', '', '');
	$save_path = str_replace($bad_char_arr, $replace_char_arr, $save_path);

	//move the temp file to the proper place
	if (move_uploaded_file($tmpfile, $save_path)) {
		$ext = pathinfo($save_path, PATHINFO_EXTENSION);
		$base = pathinfo($save_path, PATHINFO_FILENAME);
		$dir = pathinfo($save_path, PATHINFO_DIRNAME);
		$base_path = "$dir/$base";

		copy($save_path, "$base_path" . "_thumb" . "." . "$ext");
		createThumb("$base_path" . "_thumb" . "." . "$ext", $ext, 150);
		createThumb("$base_path" . "." . "$ext", $ext, 640);

		//chmod("$base_path" . "_thumb" . "." . "$ext", 0644);
		//chmod("$base_path" . "." . "$ext", 0644);

		return $save_path;
	}
	unlink($tmpfile);
	return false;
}
//----------upload user image----------
function uploadUserImage($path, $file, $tmpfile, $max, $id)
{
	   $id = secureInput($id);

       if (empty($file))
          return 1;
       if (!getimagesize($tmpfile))
          return false;
       if (checkImageSize($tmpfile, $max))
          return 2;
       if (checkAllowedExt($file))
          return 3;

	$save_path = moveUploadImage($path, $file, $tmpfile, $max);
	if (!empty($save_path)) {
		$ext = pathinfo($save_path, PATHINFO_EXTENSION);
		$base = pathinfo($save_path, PATHINFO_FILENAME);
		$dir = pathinfo($save_path, PATHINFO_DIRNAME);
		$base_path = "$dir/$base";

		$save_thumb_path = "$base_path" . "_thumb" . "." . "$ext";

		$sql = "UPDATE users SET thumb_path = '" . $save_thumb_path . "', img_path = '" . $save_path . "' WHERE id = '".$id."'";
		$res = mysql_query($sql);
			if($res){return 99;} else {return 4;}
	} else {return 5;}
}

//----------Update user image----------
function updateUserImage($path, $file, $tmpfile, $max, $id)
{
	   $id = secureInput($id);

       if (empty($file))
          return 1;
       if (!getimagesize($tmpfile))
          return false;
       if (checkImageSize($tmpfile, $max))
          return 2;
       if (checkAllowedExt($file))
          return 3;

	//look up old image path then remove the file before preceding with the new image upload
	$sql = "SELECT thumb_path,img_path FROM users WHERE id = '" . $id . "'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);
	$del = $row["thumb_path"];
	$delg = $row["img_path"];

	if (!empty($del)) {
		$dir = pathinfo($del, PATHINFO_DIRNAME);
		$ext = pathinfo($del, PATHINFO_EXTENSION);
		$base = pathinfo($del, PATHINFO_FILENAME);
		$base_path = "$dir/$base";

		unlink("$del");
		unlink("$base_path" . "_thumb" . "." . "$ext");
	}

	if (!empty($delg)) {
		$dirg = pathinfo($delg, PATHINFO_DIRNAME);
		$extg = pathinfo($delg, PATHINFO_EXTENSION);
		$baseg = pathinfo($delg, PATHINFO_FILENAME);
		$gbase_path = "$dirg/$baseg";

		unlink("$delg");
		unlink("$gbase_path" . "." . "$extg");
	}

	$save_path = moveUploadImage($path, $file, $tmpfile, $max, $id);
	if (!empty($save_path)) {
		$ext = pathinfo($save_path, PATHINFO_EXTENSION);
		$base = pathinfo($save_path, PATHINFO_FILENAME);
		$dir = pathinfo($save_path, PATHINFO_DIRNAME);
		$base_path = "$dir/$base";

		$save_thumb_path = "$base_path" . "_thumb" . "." . "$ext";
		$sql = "UPDATE users SET thumb_path = '" . $save_thumb_path . "', img_path = '" . $save_path . "' WHERE id = '" . $id . "'";
		$res = mysql_query($sql) or die(mysql_error());
		}
		if ($res)
			return 99;
				return 4;
}

//----------Delete user image----------
function deleteImage($id)
{
	$id = secureInput($id);

	//look up old image path and remove image from image folder
	$sql = "SELECT thumb_path,img_path FROM users WHERE id = '" . $id . "'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);
	$del = $row["thumb_path"];
	$delg = $row["img_path"];

	if (!empty($del)) {
		$dir = pathinfo($del, PATHINFO_DIRNAME);
		$ext = pathinfo($del, PATHINFO_EXTENSION);
		$base = pathinfo($del, PATHINFO_FILENAME);
		$base_path = "$dir/$base";

		unlink("$base_path" . "." . "$ext");
	}

	if (!empty($delg)) {
		$dirg = pathinfo($delg, PATHINFO_DIRNAME);
		$extg = pathinfo($delg, PATHINFO_EXTENSION);
		$baseg = pathinfo($delg, PATHINFO_FILENAME);
		$gbase_path = "$dirg/$baseg";

		unlink("$gbase_path" . "." . "$extg");
	}

	$sql = "UPDATE users SET thumb_path = '', img_path = '' WHERE id = '" . $id . "'";
	$res = mysql_query($sql);
	if ($res){return 99;} else {return 1;}
}

//----------Function for displaying user image on home page and admin user management page----------
function displayUserImg($id)
{
	$sql = "SELECT thumb_path FROM users WHERE id = '".$id."'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);

	if (!empty($row['thumb_path'])){
		echo "<img src='".$row['thumb_path']."' width='150' height='100' border='0' alt='' hspace='2' />";
	} else {
		//display a default image if user image does not exist
		echo "<img src='pics/no_image.gif' width='150' height='100' border='0' alt='' hspace='2' />";
	}
}

//----------Function checks if given uploaded file is ZIP----------
function fileIsZip($tmpFile,$originalFile) {
    // get the first 7 bytes
    $bytes = file_get_contents($tmpFile, FALSE, NULL, 0, 7);
    $ext = strtolower(substr($originalFile, - 4));

    // RAR magic number: Rar!\x1A\x07\x00
    // http://en.wikipedia.org/wiki/RAR
    //if ($ext == '.rar' and bin2hex($bytes) == '526172211a0700') {
    //    return TRUE;
    //}

    // ZIP magic number: none, though PK\003\004, PK\005\006 (empty archive),
    // or PK\007\008 (spanned archive) are common.
    // http://en.wikipedia.org/wiki/ZIP_(file_format)
    if ($ext == '.zip' && substr($bytes, 0, 2) == 'PK') {
        return TRUE;
    }

    return FALSE;
}

//-------Translate the file upload error code to readable message--------
function fileErrorCodeToMessage($code)
{
	switch ($code) {
		case UPLOAD_ERR_INI_SIZE:
			$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
			break;
		case UPLOAD_ERR_PARTIAL:
			$message = "The uploaded file was only partially uploaded";
			break;
		case UPLOAD_ERR_NO_FILE:
			$message = "No file was uploaded";
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$message = "Missing a temporary folder";
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$message = "Failed to write file to disk";
			break;
		case UPLOAD_ERR_EXTENSION:
			$message = "File upload stopped by extension";
			break;

		default:
			$message = "Unknown upload error";
			break;
	}
	return $message;
}

?>
