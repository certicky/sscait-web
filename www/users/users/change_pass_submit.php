<?php
DEFINE('INCLUDE_CHECK',1);
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('2');

		if (empty($_POST['oldpassword']) || empty($_POST['newpassword'])) 
		{
			die(msg(0,"Old / New password fields empty!"));
		}
		
		if(strlen($_POST['newpassword'])<5)
		{
			die(msg(0,"Password must contain more than 5 characters."));
		}
				
		$res = updatePass($_SESSION['user_id'], $_POST['oldpassword'], $_POST['newpassword']);
				
			if($res == 2){
				die(msg(0,"Incorrect old password!"));
			}
			if($res == 3){
				die(msg(0,"An error occured saving your password. Please contact the site admin."));
			}
			if($res == 99){
				die(msg(1,"Your new password has been saved."));
			}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
