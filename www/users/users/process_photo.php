<?PHP
require_once('../lib/connections/db.php');
include('../lib/functions/functions.php');

checkLogin('2');

$id = 0;
if (isset($_GET['id'])){
	if (is_numeric($_GET['id'])){
		$id = strip_tags($_GET['id']);}
		}

$action = "";
if (isset($_GET['action'])){
	$action = strip_tags($_GET['action']);
		}	
			
	if ($action == 'delete'){
		$res = deleteImage($id);
			//if successful
			if ($res == 99){
				header("Location: manage_photo.php?id=$id&message=Photo successfully deleted.");
				}
			if ($res == 1){
				header("Location: manage_photo.php?id=$id&error=An error occured while trying to delete the photo.");
				}
	}
	
if (array_key_exists('addphoto', $_POST)) {
	$id = $_POST['id'];
	$imgpth = "pics/";
		$res = uploadUserImage($imgpth, $_FILES["picture"]["name"], $_FILES["picture"]["tmp_name"], $_POST["max"], $_POST['id']);
	
		if ($res == 99) {
			header("Location: upload_photo.php?id=$pid&message=Photo uploaded successfully.");
		} elseif ($res == 1) {
			header("Location: upload_photo.php?id=$pid&error=Image field empty.");
		} elseif ($res == 2) {
			header("Location: upload_photo.php?id=$pid&error=Your image has exceeded the 300mb size limit required. Please select a smaller image file.");
		} elseif ($res == 3) {
			header("Location: upload_photo.php?id=$pid&error=Unknown image extension. Images must be in jpg, jpeg, gif or png formats.");
		} elseif ($res == 4) {
			header("Location: upload_photo.php?id=$pid&error=Unable to save image file. Please try again or contact site admin.");
		} elseif ($res == 5) {
			header("Location: upload_photo.php?id=$pid&error=Unable to save image file. Please try again or contact site admin.");
		} else {
			header("Location: upload_photo.php?id=$pid&error=There was an error uploading your image. Please try again.");
		}
	}
	
if (array_key_exists('updatephoto', $_POST)) {
	$id = $_POST['id'];
	$imgpth = "pics/";
		$res = updateUserImage($imgpth, $_FILES["picture"]["name"], $_FILES["picture"]["tmp_name"], $_POST["max"], $_POST['id']);
	
		if ($res == 99) {
			header("Location: update_photo.php?id=$pid&message=Photo updated successfully.");
		} elseif ($res == 1) {
			header("Location: update_photo.php?id=$pid&error=Image field empty.");
		} elseif ($res == 2) {
			header("Location: update_photo.php?id=$pid&error=Unknown image extension. Images must be in jpg, jpeg, gif or png formats.");
		} elseif ($res == 3) {
			header("Location: update_photo.php?id=$pid&error=Your image has exceeded the 300mb size limit required. Please select a smaller image file.");
		} elseif ($res == 4) {
			header("Location: update_photo.php?id=$pid&error=Unable to update image. Please try again, or contact the site administrator.");
		} else {
			header("Location: update_photo.php?id=$pid&error=There was an error uploading your image. Please try again.");
		}
	}
?>