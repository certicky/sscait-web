<?PHP
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

// connect to db  
$conn = mysql_connect($db_host, $db_username, $db_password) or die('Error connecting to mysql');   
$db = mysql_select_db($db_database,$conn) or die('Unable to select database!');    

?>
