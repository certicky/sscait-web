<?php
DEFINE('INCLUDE_CHECK',1);
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

	// we check if everything is filled in and perform checks

    if ($allowNameUpdates) {
        if (!$_POST['name']) {
            die(msg(0, "<p>Please enter your full name.</p>"));
        }

        if (!nameCheck($_POST['name'])) {
            die(msg(0, "<p>Forbidden characters in full name. Please use only alphanumeric characters and spaces.</p>"));
        } elseif (strlen($_POST['name']) < 5) {
            die(msg(0, "<p>Full name must be at least 5 characters long.</p>"));
        }
    }

    if($_POST['student'] != '0' && $_POST['student'] != '1')
	{
		die(msg(0,"<p>Are you a student? Value missing.</p>"));
	}
	if(!nameCheck($_POST['school']))
	{
		die(msg(0,"<p>Forbidden characters in school name. Please use only alphanumeric characters and spaces.</p>"));
	}

	elseif(strlen($_POST['bot_description'])>255)
	{
		die(msg(0,"<p>Description must have at least 255 characters.</p>"));
	}

	session_start();
	$getuser = getUserRecords($_SESSION['user_id']);
	$usr = $getuser[0];

	$sql = "UPDATE fos_user SET "
	    . ($allowNameUpdates ? "full_name='".secureInput($_POST['name'])."'," : '')
	    . "student='".$_POST['student']."',school='".secureInput($_POST['school'])."',bot_description='".secureInput($_POST['bot_description'])."',last_update_time=NOW() WHERE id='".$usr['id']."';";
	$res = mysql_query($sql) or die (mysql_error());
	if($res){
		die(msg(1,"Your profile info has been updated."));
	}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
