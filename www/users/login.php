<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/settings_server.php');

// timestamp 1482138000 = 19.12.2016 08:00 CET
//if (time() > 1482138000) {
//	die("Submission system is temporarily closed for the competitive phase of SSCAIT.");
//}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>

	<!-- Style for forms -->
	<link rel="stylesheet" href="css/pure-min.css">

	<title>Log In</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />

	<script type="text/javascript" src="js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="js/script.js"></script>

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
    <?php
    if (!empty($GLOBALS['loggingInEnabled'])) {
        ?>
        <?php /* <div style="">Registration and bot submission is closed right now. It will be re-enabled in January 2016.</div> */ ?>
        <table align="center" width="100%" cellspacing="1" cellpadding="1" border="0">
            <tr>
                <td align="left"><a href="login.php">Log in</a> | <a href="register.php">Register</a> |
                    <a href="pass_reset.php">Reset Password</a>
            </tr>
        </table>
        <hr/>
        <?php if (!empty($error)) {
            echo "<div class='error'>" . $error . "</div>";
        } ?>
        <form class="pure-form pure-form-aligned" id="loginForm" method="post" action="login_submit.php">
            <table align="center" width="50%" cellspacing="1" cellpadding="1" border="0">
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td>
                        <label for="username">Email:</label>
                    </td>
                    <td>
                        <input onclick="this.value='';" name="username" type="text" size="25" maxlength="60" value="<?php if (isset($_POST['username'])) {
                            echo $_POST['username'];
                        } ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="password">Password:</label>
                    </td>
                    <td>
                        <input name="password" type="password" size="25" maxlength="15"/>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input class="pure-button pure-button-primary" type="submit" name="submit" value="Login"/><img id="loading" src="images/loading.gif" alt="Logging in.."/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div id="error">&nbsp;</div>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td class="normalLinks">
                        <a href="register.php">Registration</a> |
                        <a href="pass_reset.php">Password recovery</a>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    } else {
        ?>
        <div class="error" style="padding-top: 15px;">Logins and updates are disabled during a tournament or maintenance phase.</div>
        <?php
    }
    ?>
</body>
</html>
