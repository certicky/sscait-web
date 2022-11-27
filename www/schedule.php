<div id="following_matches_wrapper">Game schedule:</div>
<table id="following_matches">
<?php

include("./includes/getPortrait.php");
include("./includes/shortenName.php");

// DB connection
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

if (!$GLOBALS["eliminationBracketPhase"]) {

	$res = mysql_query("SELECT * FROM games WHERE result='unfinished' ORDER BY game_id ASC LIMIT 4;");
	$first = true;
	while ($l = mysql_fetch_assoc($res)) {
	        $res1 = mysql_query("SELECT full_name,bot_race,id FROM fos_user WHERE id='".$l['bot1']."';");
	        $res2 = mysql_query("SELECT full_name,bot_race,id FROM fos_user WHERE id='".$l['bot2']."';");
	        $n1 = mysql_fetch_assoc($res1);
	        $n2 = mysql_fetch_assoc($res2);
	        if ($first) {$class='class="running"'; $first=false;} else {$class="";}
	
	        echo '<tr '.$class.'><td class="host"><a href="./index.php?action=scores#'.urlencode($n1['full_name']).'">'.getSmallPortrait($n1['id'],$n1['bot_race'])." ".shortenName($n1['full_name'],13).'</a></td><td class="vs">vs.</td><td class="guest"><a href="./index.php?action=scores#'.urlencode($n2['full_name']).'">'.shortenName($n2['full_name'],13)." ".getSmallPortrait($n2['id'],$n2['bot_race']).'</a></td></tr>';
	}
}
?>
</table>
<div id="recent_achievements_wrapper" style="border-bottom: solid 1px black;">
	Recent Achievements:
</div>
<table id="recent_achievements">
<?php
$res = mysql_query("SELECT datetime, title, full_name, achievements.type FROM achievements, achievement_texts, fos_user WHERE achievements.type = achievement_texts.type AND bot_id=id ORDER BY datetime DESC LIMIT 4;");
if (mysql_num_rows($res)>0)
while($line = mysql_fetch_assoc($res)) {
	//$message = "<b><a href=\"./index.php?action=results#".urlencode($line['name'])."\">".$line['name']."</a></b> has unlocked the <b><a href=\"./index.php?action=achievements\">".$line['title']."</a></b> achievement.";
	$message = "<b><a href=\"./index.php?action=achievements#".urlencode($line['type'])."\">".$line['title']."</a></b> achievement for <b><a href=\"./index.php?action=scores#".urlencode($line['full_name'])."\">".$line['full_name']."</a></b>.";
	echo '<tr><td class="achievement_datetime">'.date("d.m. H:i",$line['datetime']).'</td><td class="achievement_message">'.$message.'</td></tr>';
}
?>
</table>
