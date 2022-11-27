<?php
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

	<title>SSCAI Bot Registration</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />

	<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="js/script.js"></script>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#regForm').submit(function(e) {
				register();
				e.preventDefault();
			});
		});
	</script>

	<!-- Autocomplete -->
	<link rel="stylesheet" href="css/jquery-ui.css">
	<script src="js/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		var ac_config = {
			source: "schools_autocomplete.php",
			minLength:1
		};
		$("#school").autocomplete(ac_config);
	});
	</script>

	<style type="text/css">
		label span {
			font-size: 80%;
			color: #545454;
		}
	</style>

</head>
<body>
	<?php
	if (!isset($_GET['flags'])) {
	?>
	<table align="center" width="100%" cellspacing="1" cellpadding="1" border="0">
		<tr>
			<td align="left"><a href="login.php">Log in</a> | <a href="register.php">Register</a> | <a href="pass_reset.php">Reset Password</a>
		</tr>
	</table>
	<hr/>
	<?php
	}
	?>

	<div class="done"><p>Registration successful! <a href="login.php">Click here</a> to login.</p></div><!--close done-->
	<div class="form">
	<form class="pure-form pure-form-aligned" id="regForm" action="reg_submit.php" method="post" enctype="multipart/form-data">
		<table align="center" width="60%" cellspacing="1" cellpadding="1" border="0">
		  <tr>
			<td colspan="2" ></td>
		  </tr>
		  <tr>
			<td>
				<label for="username">Email:</label>
			</td>
			<td>
			<input name="username" type="text" size="25" maxlength="40" value="<?php if(isset($_POST['username'])){echo $_POST['username'];}?>"/>
			</td>
		  </tr>
		  <tr>
			<td>
				<label for="name">Bot name or your full name:</label>
			</td>
			<td>
			<input name="name" type="text" size="25" maxlength="40" value="<?php if(isset($_POST['name'])){echo $_POST['name'];}?>"/>
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
			<td>
				<label for="student">I am a student (optional):</label>
			</td>
			<td>
				<select name="student">
					<option <?php if(!isset($_POST['student']) || $_POST['student'] == "0"){echo 'selected="selected"';} ?> value="0">No</option>
					<option <?php if(isset($_POST['student']) && $_POST['student'] == "1"){echo 'selected="selected"';} ?>value="1">Yes</option>
				</select>
			</td>
		  </tr>
		  <tr>
			<td>
				<label for="name">If you're a student, where do you study? (optional):</label>
			</td>
			<td>
			<input id="school" name="school" type="text" size="25" maxlength="80" value="<?php if(isset($_POST['school'])){echo $_POST['school'];}?>"/>
			</td>
		  </tr>

		  <tr><td colspan="2"><hr/></td><td></td></tr>

		  <tr>
			<td>
				<label for="bot_type">Type of the bot:</label>
			</td>
			<td>
				<select name="bot_type">
					<option <?php if(isset($_POST['bot_type']) && $_POST['bot_type'] == "JAVA_MIRROR"){echo 'selected="selected"';} ?> value="JAVA_MIRROR">JAR made with BWMirror (Java)</option>
					<option <?php if(isset($_POST['bot_type']) && $_POST['bot_type'] == "JAVA_JNI"){echo 'selected="selected"';} ?>value="JAVA_JNI">JAR made with JNIBWAPI (Java)</option>
					<option <?php if(isset($_POST['bot_type']) && $_POST['bot_type'] == "AI_MODULE"){echo 'selected="selected"';} ?>value="AI_MODULE">DLL compiled with BWAPI (C++)</option>
					<option <?php if(isset($_POST['bot_type']) && $_POST['bot_type'] == "EXE"){echo 'selected="selected"';} ?>value="EXE">EXE compiled with BWAPI (C++)</option>
				</select>
			</td>
		  </tr>

		  <tr>
			<td>
				<label for="race">Bot's race:</label>
			</td>
			<td>
				<select name="race">
					<option <?php if(isset($_POST['race']) && $_POST['race'] == "Terran"){echo 'selected="selected"';} ?> value="Terran">Terran</option>
					<option <?php if(isset($_POST['race']) && $_POST['race'] == "Protoss"){echo 'selected="selected"';} ?>value="Protoss">Protoss</option>
					<option <?php if(isset($_POST['race']) && $_POST['race'] == "Zerg"){echo 'selected="selected"';} ?>value="Zerg">Zerg</option>
					<option <?php if(isset($_POST['race']) && $_POST['race'] == "Random"){echo 'selected="selected"';} ?>value="Random">Random</option>
				</select>
			</td>
		  </tr>


		  <tr>
			<td>
				<label for="description">Bot's description:</label>
			</td>
			<td>
				<textarea name="description"><?php if (isset($_POST['description'])) echo $_POST['description']; ?></textarea>
			</td>
		  </tr>

		  <tr>
			<td>
				<label for="bot_binary">Compiled Bot with BWAPI.dll (max size: <?php echo ini_get('upload_max_filesize');?>):<br/>
				<span>ZIP containing <b>(1)</b> <b>Runnable JAR</b>, <b>EXE</b> or <b>DLL</b> file, <b>(2)</b> the "<b>BWAPI.dll</b>" file from your computer (it's probably in your "Chaoslauncher" folder) and <b>(3)</b> anything else that you need to have in the "Starcraft/bwapi-data/AI" folder (optional). For example, if you're using some custom JNIBWAPI bridge DLL, you should include it here.</span></label>
			</td>
			<td>
				<input name="bot_binary" type="file" accept=".zip,.ZIP" value="<?php if (isset($_POST['bot_binary'])) echo $_POST['bot_binary']; ?>" />
			</td>
		  </tr>
		  <tr>
			<td>
				<label for="bot_sources">Bot Sources (max size: <?php echo ini_get('upload_max_filesize');?>):<br/><span>ZIP containing the bot's source code. This will never be published by us.</span></label>
			</td>
			<td>
				<input name="bot_sources" type="file" accept=".zip,.ZIP" value="<?php if (isset($_POST['bot_binary'])) echo $_POST['bot_binary']; ?>" />
			</td>
		  </tr>
		  <tr>
			<td>
				<label for="bot_additional_files">Additional Files  (optional, max size: <?php echo ini_get('upload_max_filesize');?>):<br/><span>If you need to have something in "Starcraft/bwapi-data/read" folder, you can upload it here in one ZIP archive.</span></label>
			</td>
			<td>
				<input name="bot_additional_files" type="file" accept=".zip,.ZIP" value="<?php if (isset($_POST['bot_binary'])) echo $_POST['bot_binary']; ?>" />
			</td>
		  </tr>

				<?php
				if (isset($_GET['flags'])) {
					//echo '<tr><td colspan="2"><hr/></td><td></td></tr>';
					//echo '<tr><td><label for="flags">Custom flags:</label></td><td><input type="text" name="flags" id="flags" value="'.$_GET['flags'].'" /></td></tr>';
					echo '<input type="hidden" name="flags" id="flags" value="'.$_GET['flags'].'" />';
				}
				?>


		  <tr><td colspan="2"><hr/></td><td></td></tr>

		   <tr>
			<td>&nbsp;</td>
			<td>
				<input class="pure-button pure-button-primary" type="submit" name="register" value="Register" /><img id="loading" src="images/loading.gif" alt="uploading.." />
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
