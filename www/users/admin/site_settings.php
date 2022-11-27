<?PHP
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('1');

$sitesettings = getSiteSettings();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Manage Site Settings</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen" />

	<script type="text/javascript" src="../js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="../js/script.js"></script>
		<script type="text/javascript">
		$(document).ready(function(){
	
			$('#sitesetForm').submit(function(e) {
				editsiteset();
				e.preventDefault();	
			});	
		});

	</script>
</head>

<body>
	<div align="right"><a href="index.php">Home</a> | <a href="change_pass.php">change password</a> | <a href="edit_profile.php">Edit Profile</a> | <a href="manage_users.php">Manage Users</a> | <a href="site_settings.php">Manage Site Settings</a> | <a href="log_off.php?action=logoff">sign out</a></div>
		<div class="done"><p>Site settings successfully edited.</p><p><a href="site_settings.php">Click here</a> to continue editing site settings.</p></div><!--close done-->
			<div class="form">
			<h3>Edit Site Settings</h3>
			<hr/>
				<table border="0" cellspacing="2" cellpadding="2" align="center">
					<form id="sitesetForm" action="edit_siteset_submit.php" method="post">
						<tr>
							<td><label for="site url">Site URL:</label></td><td><input type="text" name="site_url" value="<?php if(isset($sitesettings[0]['site_url'])){echo $sitesettings[0]['site_url'];}?>"/></td>
						</tr>
						<tr>
							<td><label for="site url">Site Email:</label></td><td><input type="text" name="site_email" value="<?php if(isset($sitesettings[0]['site_email'])){echo $sitesettings[0]['site_email'];}?>"/></td>
						</tr>
						<tr>
							<td colspan="2"><hr/></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><input type="submit" name="submit" value="Edit Site Settings" /><img id="loading" src="../images/loading.gif" alt="Updating.." /></td>
						</tr>
						<tr>
							<td colspan="2"><div id="error">&nbsp;</div></td>
						</tr>
					</form>
				</table>
			</div><!--close form-->
</body>
</html>