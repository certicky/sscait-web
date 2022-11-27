<?PHP
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('2');

$message="";
if (isset($_GET['message'])){
	$message = strip_tags($_GET['message']);
	}
	
$error="";
if (isset($_GET['error'])){
	$error = strip_tags($_GET['error']);
	}
	
$getuser = getUserRecords($_SESSION['user_id']);
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Upload Photo</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen" />

	<script type="text/javascript" src="../js/script.js"></script>
</head>

<body>
	<div align="right"><a href="index.php">Home</a> | <? if (!empty($getuser[0]['thumb_path'])){echo "<a href='manage_photo.php'>Manage My Photo</a> | ";} else {echo "<a href='upload_photo.php'>Upload Photo</a> | ";} ?><a href="change_pass.php">change password</a> | <a href="edit_profile.php">Edit Profile</a> | <a href="log_off.php?action=logoff">sign out</a></div></td>
	<p><?php if(empty($getuser[0]['first_name']) || empty($getuser[0]['last_name'])){echo $getuser[0]['username'];} else {echo $getuser[0]['first_name']." ".$getuser[0]['last_name'];} ?>, add your photo.</p>
		<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ECECFB">
		<form name="addphoto" method="post" enctype="multipart/form-data" action="process_photo.php">
			<tr>
				<td colspan="2">
				<?php 
					if ( isset( $error ) ) { echo '<p align="center"><span style="color:#f40000; font-weight:bold;">' . $error . '</span></p>';}
					if ( isset( $message ) ) { echo '<p align="center"><span style="color:#008c00; font-weight:bold;">' . $message . '</span></p>';}
				?>
				</td>
			</tr>			
			<input type="hidden" name="addphoto" value="1" />
			<input type="hidden" name="id" value="<?= $getuser[0]['id'];?>" />
			<tr>
				<td><label for="image">Upload photo:</label></td>
				<td><input type="file" name="picture"  id="file" size="30" value="" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
				<input type="hidden" name="max" value="300000" />
				<input type="submit" name="submit" value="Upload Photo"/>
				</td>
			</tr>
		</form>
		</table>
</body>
</html>