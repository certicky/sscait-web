<?php
DEFINE('INCLUDE_CHECK',1);
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('1');	

	// we check if everything is filled in and perform checks
	if($_POST['phone'] && !validateNumeric($_POST['phone']))
		{
			die(msg(0,"Phone numbers must be of numeric type only."));
		}
	if($_POST['email'] && validateEmail($_POST['email']))
		{
			die(msg(0,"Invalid Email!"));
		}
	if($_POST['email'] && uniqueEmail($_POST['email']))
		{
			die(msg(0,"Email already in database. Please select another email address."));
		}
		
		$res = editUser($_SESSION['user_id'],$_POST['email'],$_POST['first_name'],$_POST['last_name'],$_POST['dialing_code'],$_POST['phone'],$_POST['city'],$_POST['country']);
			
			if($res == 4){
				die(msg(0,"An internal error has occured. Please contact the site admin!"));
			}
			if($res == 99){
				die(msg(1,"Profile updated successfully!"));
			}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
