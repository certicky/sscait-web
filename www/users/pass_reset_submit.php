<?php
DEFINE('INCLUDE_CHECK',1);
require_once('lib/connections/db.php');
include('lib/functions/functions.php');

//For password recovery

	// we check if everything is filled in and perform checks

	if(empty($_POST['email']))
		{
			die(msg(0,"Please enter your email address."));
		}
	if(validateEmail($_POST['email']))
		{
			die(msg(0,"Invalid Email!"));
		}
		else{
			$res = pass_recovery($_POST['email']);
				if($res == 1){
					die(msg(0,"There was an error sending your new password. Please contact the site admin."));
				}
				if($res == 2){
					die(msg(0,"There was an error with the database. Please contact the site admin."));
				}
				if($res == 3){
					die(msg(0,"The email entered does not match any in our database. Please <a href='register.php'>register here</a> to start using our services."));
				}
				if($res == 99){
					die(msg(1,"Success! Check your email for more information."));
				}
		}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
