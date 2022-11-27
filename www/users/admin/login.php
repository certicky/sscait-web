<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Admin Log In</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen" />
	
	<script type="text/javascript" src="../js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="../js/script.js"></script>
		
	<script type="text/javascript">
		$(document).ready(function(){
	
			$('#loginForm').submit(function(e) {
				login();
				e.preventDefault();	
			});	
		});

	</script>

</head>
<body>
	<? if(!empty($error)){echo "<div class='error'>".$error."</div>";}?>
	<p>ADMIN LOGIN</p>
	<p>Use the form below to log in to your account.</p>
	<hr/>
	<form id="loginForm" method="post" action="login_submit.php">
		<table align="center" width="50%" cellspacing="1" cellpadding="1" border="0">
		  <tr>
			<td colspan="2" ></td>
		  </tr>
		  <tr>
			<td>
				<label for="username">Username:</label>
			</td>
			<td>
				<input onclick="this.value='';" name="username" type="text" size="25" maxlength="8" value="<?php if(isset($_POST['username'])){echo $_POST['username'];}?>"/>
			</td>
		  </tr>
		  <tr>
			<td>
				<label for="password">Password:</label>
			</td>
			<td>
				<input name="password" type="password" size="25" maxlength="15" />
			</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>
				<input type="submit" name="submit" value="Login" /><img id="loading" src="../images/loading.gif" alt="Logging in.." />
			</td>
		  </tr>
		  <tr>
			<td colspan="2"><div id="error">&nbsp;</div></td>
		  </tr>
		</table>
	</form>
</body>
</html>
