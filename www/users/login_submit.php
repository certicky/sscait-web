<?php
require_once('lib/connections/db.php');
include('lib/functions/functions.php');

$returnURL = "users/index.php";

//For login

	// we check if everything is filled in and perform checks

	if(empty($GLOBALS['loggingInEnabled']) || empty($_POST['username']) || empty($_POST['password']))
	{
		die(msg(0,"Username and / or password fields empty!"));
	}

	else
		{
			$res = login($_POST['username'],$_POST['password']);
				if ($res == 1){
					die(msg(0,"Username and / or password incorrect!"));
				}
				//if ($res == 2){
				//	die(msg(0,"Sorry! Your account has been suspended!"));
				//}
				if ($res == 3){
					die(msg(0,"Sorry! Your account has not been activated. Please check your email's inbox or spam folder for a link to activate your account."));
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
