<?PHP
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');
include('../lib/functions/ps_pagination.php');

checkLogin('1');

$message="";
if(isset($_GET['message'])){
	$message = strip_tags($_GET['message']);
	}
	
$error="";
if(isset($_GET['error'])){
	$error = strip_tags($_GET['error']);
	}
	
$getuser = getUserRecords($_SESSION['user_id']);
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?=$getuser[0]['username'];?>'s Home Page.</title>
<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen" />
</head>

<body>
	<div align="right"><a href="index.php">Home</a> | <a href="change_pass.php">change password</a> | <a href="edit_profile.php">Edit Profile</a> | <a href="manage_users.php">Manage Users</a> | <a href="site_settings.php">Manage Site Settings</a> | <a href="log_off.php?action=logoff">sign out</a></div>
	<p><?php if(empty($getuser[0]['first_name']) || empty($getuser[0]['last_name'])){echo $getuser[0]['username'];} else {echo $getuser[0]['first_name']." ".$getuser[0]['last_name'];} ?>, manage users.</p>
	<?
	  //Select all users and display paginated results
		$sql = "SELECT * FROM users WHERE level_access != 1"; 
		$res = mysql_query($sql) or die(mysql_error());
		$numRows = mysql_num_rows($res);
		if ((mysql_num_rows($res)) > 0){
		$pager = new PS_Pagination($conn, $sql, 10, 5, "");
	  ?>
		<p>There are <?=$numRows;?> users on this site.</p>
		<? if (!empty($message)){echo "<div class='message'>".$message."</div>";} ?>
		<? if (!empty($error)){echo "<div id='error'>".$error."</div>";} ?>
		<table width="100%" border="1" cellspacing="1" cellpadding="1">
			<tr>
				<td>Username</td><td>Names</td><td>Email</td><td>Phone</td><td>City</td><td>Country</td><td>Registration Date</td><td>Status</td><td>Action</td>
			</tr>
			<? 
			   $countLoop = 0;
			   $rs = $pager->paginate();
				if(!$rs) die(mysql_error());
					while($row = mysql_fetch_array($rs)){ 
					 if($row['active'] == 1){$active = "<span style='color:#008040;'>Active</span>";}
					 if($row['active'] == 2){$active = "<span style='color:#f40000;'>Suspended</span>";}
			?>	
			<tr>
				<td><?=$row['username'];?></td><td><?=$row['first_name']." ".$row['last_name'];?></td><td><?=$row['email'];?></td><td><?=$row['dialing_code']." ".$row['phone'];?></td><td><?=$row['city'];?></td><td><?=$row['country'];?></td><td><?=$row['reg_date'];?></td><td><?=$active;?></td><td><a href="edit_user.php?id=<?=$row['id'];?>">Edit</a> | <a href="admin_actions_submit.php?id=<?=$row['id'];?>&action=delete">Delete</a> | <a href="change_user_pass.php?id=<?=$row['id'];?>">Change Password</a> | <? if($row['active']==1){echo "<a href='admin_actions_submit.php?id=".$row['id']."&action=suspend'>Suspend</a>";}?><? if($row['active']== 2){echo "<a href='admin_actions_submit.php?id=".$row['id']."&action=unsuspend'>Unsuspend</a>";} ?></td>
			</tr>
			<? $countLoop++; } ?>
		</table>
		<table>
			<tr>
				<td><?= $pager->renderFullNav();?></td>
			<tr>
		</table>
			<? } else {	echo "<fieldset><p>There are currently no registered users!</p></fieldset>";} ?>
</body>
</html>