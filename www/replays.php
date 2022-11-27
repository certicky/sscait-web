<?php
function listFolderFiles($dir, $level){
    if (stripos($dir,'competitive') == True) return;
    $ffs = scandir($dir);
    rsort($ffs);
    $expl = explode("/",$dir);
    $name = $expl[sizeof($expl)-1];
    if ($name != '') echo '<h'.($level+2).'>'.$name.': </h'.($level+2).'>';
    echo '<ul>';
    $counter = 0;
    foreach($ffs as $ff){
        $counter += 1;
        if ($counter > 50 && $level > 0) break;
        if($ff != '.' && $ff != '..'){
            
            if(is_dir($dir.'/'.$ff)) {
		listFolderFiles($dir.'/'.$ff,$level+1);
            } else {
                echo '<li><a href="'.$dir.'/'.$ff.'">'.$ff.'</a></li>';
            }
        }
    }
    echo '</ul>';
}
?><!DOCTYPE html>
<html>
<head>
<title>SSCAIT Temporary Replays/Errors Log</title>
<style>
h3 {
	border-top: solid 1px gray;
}
ul {
	margin-bottom: 2ex;
}
</style>
</head>
<body>
<?php listFolderFiles('./Replays',0); ?>
</body>
</html>


