<?php
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

$returnURL = "index.php";

//For login

	// we check if everything is filled in and perform checks
	
	if(!$_POST['username'] || !$_POST['password'])
	{
		die(msg(0,"Username and / or password fields empty!"));
	}

	else
		{
			$res = adminLogin($_POST['username'],$_POST['password']);
				if ($res == 1){
					die(msg(0,"Unknown User! You are not authorised to log in as an admin."));
				}
				if ($res == 2){
					die(msg(0,"Username and / or password incorrect!"));
				}
				if ($res == 99){
					echo(msg(1,$returnURL));
				}
		}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}
	
?>
