<?PHP
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

checkLogin('2');
$getuser = getUserRecords($_SESSION['user_id']);
$usr = $getuser[0];

function listOneFolder($folderPath,$folderReadableName,$binaryPath="N/A",$elementId='',$usr) {
	$binaryFound = FALSE;
	$directory = opendir($folderPath);
	$dirArray = array();
	// get each entry
	while($entryName = readdir($directory)) {
		// don't list links to this dir, upper level dir and hidden files
		if (substr($entryName, 0, 1) != ".") {
			$dirArray[] = $entryName;
		}
	}
	// close directory
	closedir($directory);
	//	count elements in array
	$indexCount	= count($dirArray);
	print ("<span style=\"font-family: monospace; font-weight: bold\">$folderReadableName</span> ($indexCount files)\n");
	// sort 'em
	sort($dirArray);
	// print 'em
	print("<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" class=\"folderContents\" id=\"$elementId\">\n");
	print("<tr><th style=\"font-weight: normal; font-size: 90%; padding-bottom: 2px; font-style: italic; min-width: 300px;\">Filename</th><th style=\"font-weight: normal; font-size: 90%; padding-bottom: 2px; font-style: italic;\">Filesize</th></tr>\n");
	// loop through the array of files and print them all
	for($index=0; $index < $indexCount; $index++) {
		print("<tr>");
		if ($binaryPath != '' && basename($binaryPath) == $dirArray[$index]) {
			print("<td>$dirArray[$index] <span style=\"color: green;\">(bot's binary)</span></td>");
			$binaryFound = TRUE;
		} elseif (is_dir($folderPath.'/'.$dirArray[$index])) {
			print("<td>$dirArray[$index] (folder)</td>");
		} else {
			if ($elementId == 'read-folder') { // downloadable files in read folder
				print("<td><a target=\"_blank\" href=\"".$GLOBALS["DOMAIN_WITHOUT_SLASH"]."/bot_read_write_folder.php?bot=".$usr['full_name']."&folder=read&file=$dirArray[$index]\">$dirArray[$index]</a></td>");
			} else if ($elementId == 'write-folder') { // downloadable files in write folder
				print("<td><a target=\"_blank\" href=\"".$GLOBALS["DOMAIN_WITHOUT_SLASH"]."/bot_read_write_folder.php?bot=".$usr['full_name']."&folder=write&file=$dirArray[$index]\">$dirArray[$index]</a></td>");
			} else {
				print("<td>$dirArray[$index]</td>");
			}
		}
		print("<td>");
		print(ceil(filesize($folderPath.'/'.$dirArray[$index])/1024)." kB");
		print("</td>");
		print("</tr>\n");
	}
	if ($indexCount == 0) {
		print("<tr><td colspan=\"2\">This folder is empty.</td></tr>");
	}
	print("</table>\n");
	if (!$binaryFound && $binaryPath != 'N/A') {
		print("<p id=\"disabledError\" style=\"color: red\">Error! Bot's binary not detected. The bot cannot be enabled.</p>");
	}

}


?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


	<!-- Style for forms -->
	<link rel="stylesheet" href="../css/pure-min.css">

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=$getuser[0]['full_name'];?> at SSCAI.</title>
	<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen" />
	<script type="text/javascript" src="../js/jquery-1.6.2.js"></script>
	<script type="text/javascript" src="../js/script.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){

		$('#editprofileForm').submit(function(e) {
			editprofile();
			e.preventDefault();
		});

		$('#binaryForm').submit(function(e) {
			uploadBinary();
			e.preventDefault();
		});

		$('#additionalForm').submit(function(e) {
			uploadAdditional();
			e.preventDefault();
		});

	});
	</script>

	<!-- File Tree for Replays -->
	<script type="text/javascript" src="../js/jqueryFileTree.js"></script>
	<script type="text/javascript" src="../js/jquery.easing.js"></script>
	<link rel="stylesheet" type="text/css" href="../css/jqueryFileTree.css" media="screen" />

	<!-- Autocomplete -->
	<link rel="stylesheet" href="../css/jquery-ui.css">
	<script src="../js/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		var ac_config = {
			source: "../schools_autocomplete.php",
			minLength:1
		};
		$("#school").autocomplete(ac_config);
	});
	</script>


	<style type="text/css">
		.directoryListings td, .directoryListings th{
			vertical-align: top;
			padding-right: 20px;
			border: solid 1px #CCC;
			background: #F5F5F5;
		}
		.directoryListings {
			margin-bottom: 20px;
		}
		input, textarea {
			width: 300px;
		}
		th {
			padding-bottom: 20px;
		}
		div.table-box {
			border: solid 1px #CCC;
			border-radius: 4px;
			box-sizing: border-box;
			box-shadow: 0px 1px 3px #DDD inset;
			background: #F5F5F5;
			background: #F7F7F7;
			padding: 10px;
			margin-right: 10px;
			margin-bottom: 5px;
		}
		.formNote {
			font-size: 85%;
			width: 300px;
			color: #505050;
			padding-top: 3px;
			text-align: justify;
		}
		.folderContents {
			color: #505050;
		}
	</style>

</head>

<body>


	<div align="right" style="padding: 10px 10px 0 0;">Logged in as <?php echo $usr['full_name']; ?>. <a href="log_off.php?action=logoff">log out</a></div>
	
	<p align="center" class="done">Profile updated successfully.<br/>[ <a href="index.php">back</a> ]</p><!--close done-->
	<div class="form" style="margin-top: 20px; text-align: center; padding: 0 20px;">

	<table align="center" style="margin: 0 auto; text-align: left;">
		<tr>
			<td style="vertical-align: top; ">

				<div class="table-box">
            		<table>
            			<tr>
            				<td style="min-width: 150px;">Email:</td>
            				<td><?=$usr['email'];?></td>
            			</tr>
            			<tr>
            				<td>Password:</td>
            				<td><a href="change_pass.php">Change Password</a></td>
            			</tr>
            			<tr>
            				<td>Bot Type:</td>
            				<td><?=$usr['bot_type'];?></td>
            			</tr>
            			<tr>
            				<td>Last Update:</td>
            				<td><?=$usr['last_update_time'];?></td>
            			</tr>
            			<tr>
            				<td>Bot Enabled:</td>
            				<td>
            				<?php
            				if ($usr['bot_enabled']) {
            					echo '<span style="color: green">Enabled</span>';
            				} else {
            					echo '<span style="color: red">Disabled.</span> The bot is disabled either because it wasn\'t updated for a long time, or there\'s something wrong with it. Upload a new version or <a href="http://sscaitournament.com/index.php?action=contact">contact us</a> to enable it.';
            				}
            				?>
            				</td>
            			</tr>
            
            		</table>
            	</div>
			</td>
			<td style="vertical-align: top; ">
				<div class="table-box">
					<b>Citations:</b>
					<div style="color: #505050;">
						Whenever you write an academic publication about your bot, feel free to
						mention its involvement in SSCAIT by referencing some of the papers below. Thank you!
					</div>
					<?php include '../../includes/publicationsList.php'; ?>
				</div>
			</td>
					
		</tr>
		<tr>
			<td style="vertical-align: top; ">
				<form class="pure-form pure-form-aligned" id="editprofileForm" action="edit_profile_submit.php" method="post">
					<div class="table-box">
						<b>Profile Info:</b><br/>
						<table>
						<tr>
							<td>Bot's Profile:</td>
							<td><a href="<?php echo $GLOBALS["DOMAIN_WITHOUT_SLASH"]; ?>/index.php?action=botDetails&bot=<?php echo urlencode($usr['full_name']); ?>" target="_blank"><?php echo $GLOBALS["DOMAIN_WITHOUT_SLASH"]; ?>/index.php?action=botDetails&bot=<?php echo urlencode($usr['full_name']); ?></a></td>
						</tr>
						<tr>
							<td>Bot's Liquipedia:</td>
							<td><a href="https://liquipedia.net/starcraft/<?php echo urlencode($usr['full_name']); ?>" target="_blank">https://liquipedia.net/starcraft/<?php echo urlencode($usr['full_name']); ?></a></td>
						</tr>
						<tr>
							<td>Full Name:</td>
							<td><?php if (!empty($allowNameUpdates)) { ?>
							    <input name="name" type="text" size="25" maxlength="40" value="<?=$usr['full_name'];?>"/>
                                <?php
                                } else {
							        echo $usr['full_name'];
                                }
							    ?>
                            </td>
						</tr>
						<tr>
							<td>Bot Description:</td>
							<td><textarea name="bot_description"><?=$usr['bot_description'];?></textarea></td>
						</tr>
						<tr>
							<td>Student:</td>
							<td>
								<select name="student">
									<option <?php if($usr['student'] == '0') {echo 'selected="selected"';} ?> value="0">No</option>
									<option <?php if($usr['student'] == '1') {echo 'selected="selected"';} ?> value="1">Yes</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>School:</td>
							<td><input id="school" name="school" type="text" size="25" maxlength="80" value="<?=$usr['school'];?>"/></td>
						</tr>
						<tr>
							<td>&nbsp;</td><td><input class="pure-button pure-button-primary" type="submit" name="editprofile" value="Update User Info" /><img id="loading" src="../images/loading.gif" alt="Updating.." /></td>
						</tr>
						<tr>
							<td colspan="2"><div id="error">&nbsp;</div></td>
						</tr>

						</table>
					</div>
				</form>

			</td>
			<td style="vertical-align: top; ">
				<div class="table-box">
					<div class="directoryListings">
						<?php
							$path = $GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"].'/'.$usr['id'].'/AI/';
							listOneFolder($path,"Starcraft/bwapi-data/AI/",$usr['bot_path'],'ai-folder',$usr);
						?>
						<div style="margin: 10px 0;">
							<form class="pure-form pure-form-aligned form" id="binaryForm" action="new_binary_submit.php" method="post" enctype="multipart/form-data">
								<input type="hidden" name="user" value="<?php echo $usr['full_name']; ?>" />
								<input name="bot_binary" type="file" accept=".zip,.ZIP" value="" />
								<br/>
								<input class="pure-button pure-button-primary" style="margin-top: 3px;" type="submit" name="upload-new-binary" value="Upload new Bot Binary (ZIP)" /><img id="loading2" src="../images/loading.gif" alt="uploading.." />
								<div class="formNote">Note: Uploading new bot version will replace all the contents of Starcraft/bwapi-data/AI/ folder.</div>
							</form>
							<div id="error2">&nbsp;</div>
							<div class="done" id="binaryUploadSuccessfull">New bot version uploaded! [ <a href="./index.php">refresh</a> ]</div>
						</div>

					</div>
				</div>
				<div class="table-box">
					<div class="directoryListings">
						<?php
							$path = $GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"].'/'.$usr['id'].'/read/';
							listOneFolder($path,"Starcraft/bwapi-data/read/","N/A",'read-folder',$usr);
						?>
						<div style="margin: 10px 0;">
							<form class="pure-form pure-form-aligned form" id="additionalForm" action="new_additional_files_submit.php" method="post" enctype="multipart/form-data">
								<input type="hidden" name="user" value="<?php echo $usr['full_name']; ?>" />
								<input name="bot_additional_files" type="file" accept=".zip,.ZIP" value="" />
								<br/>
								<input class="pure-button pure-button-primary" style="margin-top: 3px;" type="submit" name="upload-new-additional-files" value="Upload Additional Files (ZIP)" /><img id="loading3" src="../images/loading.gif" alt="uploading.." />
								<div class="formNote">Note: Uploading new archive will replace all the contents of Starcraft/bwapi-data/read/ and write/ folders.</div>
							</form>
							<div id="error3">&nbsp;</div>
							<div class="done" id="additionalUploadSuccessfull">Additional files uploaded! [ <a href="./index.php">refresh</a> ]</div>
						</div>
					</div>
				</div>
				<div class="table-box">
					<div class="directoryListings">
						<?php
							$path = $GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"].'/'.$usr['id'].'/write/';
							listOneFolder($path,"Starcraft/bwapi-data/write/","N/A",'write-folder',$usr);
						?>
					</div>
				</div>
			</td>
		</tr>
	</table>
	</div>


</body>
</html>
