<?php
DEFINE('INCLUDE_CHECK',1);
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('1');

		if (empty($_POST['newpassword'])) 
		{
			die(msg(0,"New password field empty!"));
		}
		
		if(strlen($_POST['newpassword'])<5)
		{
			die(msg(0,"New password must contain more than 5 characters."));
		}
				
		$res = adminUpdatePass($_POST['id'],$_POST['newpassword']);
				
			if($res == 1){
				die(msg(0,"An error occured saving the password. Please try again."));
			}
			if($res == 99){
				die(msg(1,"New password saved."));
			}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
