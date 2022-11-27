<?php
DEFINE('INCLUDE_CHECK',1);
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('1');	

	if($_POST['site_email'] && validateEmail($_POST['site_email']))
		{
			die(msg(0,"Invalid Email!"));
		}
		
	// we check if everything is filled in and perform checks
	
	$res = updateSiteSet($_POST['site_url'],$_POST['site_email']);
	
		//if successful
		if ($res == 99){
			die(msg(1,"Site Settings updated successfully!"));
			}
			
		//if errors occured
		if($res == 2)
			{
				die(msg(0,"An error occured while updating the site settings. Please contact the site admin."));
			}

	function msg($status,$txt)
	{
		return '{"status":'.$status.',"txt":"'.$txt.'"}';
	}

?>
