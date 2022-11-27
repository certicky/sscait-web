<?PHP
DEFINE('INCLUDE_CHECK',1);
require_once('lib/connections/db.php');
include('lib/functions/functions.php');

$id = '';
if (isset($_GET['id'])){
	if (is_numeric($_GET['id'])){
		$id = secureInput($_GET['id']);}
		}
			
$new = '';
if (isset($_GET['new'])){
	$new = secureInput($_GET['new']);
	}
	
	$res = confirm_pass($id,$new);
		if ($res == 1){
			$error = "Unable to update new password. Please contact the site admin.";
			}
		if ($res == 2){
			$error = "The new password is already confirmed or is incorrect!";
			}
		if ($res == 3){
			$error = "This user does not exist.";
			}
		if ($res == 99){
			$message = "Your new password has been confirmed. You may <a href='login.php'>login</a> using it.";
			}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Confirm Password</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
	
	<script type="text/javascript" src="js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="js/script.js"></script>
	
</head>
<body>
	<table align="center" width="100%" cellspacing="1" cellpadding="1" border="0">
	  <tr>
		<td align="left"><a href="index.php">Home</a> | <a href="login.php">Log in</a> | <a href="register.php">Register</a> | <a href="pass_reset.php">Reset Password</a> | <a href="contact_us.php">Contact Us</a></td><td align="right"><a href="admin/login.php">Admin Login</a></td>
	  </tr>
	</table>
	<hr/>
				<? if(isset($error))
					{
						echo '<div class="error">' . $error . '</div>' . "\n";
					}
					   else if(isset($message)) {
							echo '<div class="message">' . $message . '</div>' . "\n";
						} 
				?>
</body>
</html>
