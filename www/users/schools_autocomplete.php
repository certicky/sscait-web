<?php
require_once('lib/connections/db.php');
include('lib/functions/functions.php');

$sql = "SELECT DISTINCT school FROM fos_user WHERE 1;";
$res = mysql_query($sql) or die(mysql_error());

$schools = array();
while ($s = mysql_fetch_assoc($res)) {
	$schools[] = array('school' => $s['school']);
}

// Cleaning up the term
$term = trim(strip_tags($_GET['term']));
// Rudimentary search
$matches = array();
foreach($schools as $school){
	if(stripos($school['school'], $term) !== false){
		// Add the necessary "value" and "label" fields and append to result set
		$school['value'] = $school['school'];
		$school['label'] = "{$school['school']}";
		$matches[] = $school;
	}
}

// Truncate, encode and return the results
$matches = array_slice($matches, 0, 5);
print json_encode($matches);


?>
