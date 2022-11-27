<?PHP
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

$action='';
	if (isset($_GET['action'])){
		$action = strip_tags($_GET['action']);}
	if ($action == 'logoff'){
		logoff();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Logged Out</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
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
	<div class="message" align="center">You have been logged out. <a href="../login.php">Click here</a> to log in.</div>
</body>
</html>
