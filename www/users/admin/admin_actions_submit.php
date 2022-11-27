<?php
DEFINE('INCLUDE_CHECK',1);
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('1');

$id=0;
if(isset($_GET['id'])){
	if(is_numeric($_GET['id'])){
		$id = strip_tags($_GET['id']);
		$id = secureInput($_GET['id']);
		}
	}

$action="";
if(isset($_GET['action'])){
	$action = strip_tags($_GET['action']);
	$action = secureInput($_GET['action']);
	}
	
	if($action == "suspend"){
		$res = suspendUser($id);
				
			if($res == 1){
				header("Location: manage_users.php?error=An error occured while attempting to suspend user. Please try again.");
			}
			if($res == 2){
				header("Location: manage_users.php?error=An error occured selecting user to suspend.");
			}
			if($res == 99){
				header("Location: manage_users.php?message=User suspended.");
			}
	}
	
	if($action == "unsuspend"){
		$res = unsuspendUser($id);
				
			if($res == 1){
				header("Location: manage_users.php?error=An error occured while attempting to unsuspend user. Please try again.");
			}
			if($res == 2){
				header("Location: manage_users.php?error=An error occured selecting user to unsuspend.");
			}
			if($res == 99){
				header("Location: manage_users.php?message=User unsuspended.");
			}
	}
	
	if($action == "delete"){
		$res = deleteUser($id);
				
			if($res == 1){
				header("Location: manage_users.php?error=An error occured while attempting to delete user. Please try again.");
			}
			if($res == 2){
				header("Location: manage_users.php?error=An error occured selecting user to delete.");
			}
			if($res == 99){
				header("Location: manage_users.php?message=User deleted.");
			}
	}

?>
