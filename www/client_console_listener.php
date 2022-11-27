<?php

$hostMachineIp = $_SERVER['SERVER_ADDR'];

$msg = str_ireplace("C:\\TM","",urldecode($_GET["message"]));
$addr = $_GET["address"];

function addLineToConsoleFile($fileName, $line, $max = 15) {
    $file = array_filter(array_map("trim", file($fileName)));
    $file = array_slice($file, 0, $max);
    count($file) >= $max and array_shift($file);
    array_push($file, $line);
    file_put_contents($fileName, implode(PHP_EOL, array_filter($file)));
}

// process only messages from host machine
if (stripos($addr,$hostMachineIp) !== FALSE) {
	// keep last 15 of them in the file
	addLineToConsoleFile("./client_console.txt", $msg);
}


?>
