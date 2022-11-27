<?PHP
require_once('lib/connections/db.php');
include('lib/functions/functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	
	<!-- Style for forms -->
	<link rel="stylesheet" href="css/pure-min.css">
	
	<title>Password Reset</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
	
	<script type="text/javascript" src="js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="js/script.js"></script>
		
	<script type="text/javascript">
		$(document).ready(function(){
	
			$('#passreset').submit(function(e) {
				passreset();
				e.preventDefault();	
			});	
		});
	</script>

</head>
<body>
	<table align="center" width="100%" cellspacing="1" cellpadding="1" border="0">
	  <tr>
		<td align="left"><a href="login.php">Log in</a> | <a href="register.php">Register</a> | <a href="pass_reset.php">Reset Password</a>
	  </tr>
	</table>
	<hr/>
	<p>Enter your email address below.</p>
	<hr/>
	  <div class="done"><H3>New password sent.</H3><p>Check your inbox / junk mail folder for a link to reset your password.</p></div><!--close done-->
	  <div class="form">
		<form class="pure-form pure-form-aligned" id="passreset" action="pass_reset_submit.php" method="post">
		<table align="center" width="50%" cellspacing="1" cellpadding="1" border="0">
		  <tr>
			<td><label for="email">Your Email:</label></td>
			<td><input onclick="this.value='';" name="email" type="text" size="25" maxlength="30" value="<?php if(isset($_POST['email'])){echo $_POST['email'];}?>" /></td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>
				<input class="pure-button pure-button-primary" type="submit" value="Submit" /><img id="loading" src="images/loading.gif" alt="Sending.." />
			</td>
		  </tr>
		  <tr>
			<td colspan="2"><div id="error">&nbsp;</div></td>
		  </tr>
		</table>
		</form>          
	</div><!--close form-->
</body>
</html>
