<h2>Registered Bots</h2>
<div>Once you register and upload your bot sources, we will try to compile them. If compilation succeeds, the bot will appear in this list. Bots that have <i>"(example)"</i> sign next to their name are non-competitive. They will not be able to score any points in the tournament.</div>

<table id="bot_list" class="sortable">
<thead>
<tr>
<td>Name</td><td>Race</td><td>School</td><td>Bot Description</td><td>Status</td><td>Win Rate<br/>(over last 48 hours)</td><td>Draws<br/>(over last 48 hours)</td><td style="width: 140px;">Achievements</td>
</tr>
</thead>
<tbody>
<?php

// Print out all the enabled and ready bots
$res = mysql_query("SELECT * FROM fos_user WHERE enabled='1' ORDER BY SUBSTRING(name, LENGTH(name), 5) DESC, name ASC;");
while ($l = mysql_fetch_assoc($res)) {
	$name = $l['name'];
	$school = $l['school'];
	$race = $l['race'];
	$desc = $l['description'];
	if ($l['bot_ready'] == '1') {
		$status = '<span class="ready">Ready</span>';
	} else {
		$status = '<span class="disabled">Disabled</span>';
	}
	$winRes =  mysql_query("SELECT count(game_id) FROM `games` WHERE datetime > ".(time()-172800)." AND ((bot1='".$l['id']."' AND result='1') or (bot2='".$l['id']."' AND result='2'))");
	$lossRes = mysql_query("SELECT count(game_id) FROM `games` WHERE datetime > ".(time()-172800)." AND ((bot1='".$l['id']."' AND result='2') or (bot2='".$l['id']."' AND result='1'))");
	$drawsRes = mysql_query("SELECT count(game_id) FROM `games` WHERE datetime > ".(time()-172800)." AND result='draw' AND ((bot1='".$l['id']."') OR (bot2='".$l['id']."'))");
	$wins = mysql_fetch_row($winRes);
	$losses = mysql_fetch_row($lossRes);
	$draws = mysql_fetch_row($drawsRes);
	$winRate = round($wins[0] / ($wins[0]+$losses[0]+$draws[0]) * 100,2)."% <span class=\"gameCount\">(of&nbsp;".(intval($wins[0])+intval($losses[0])+intval($draws[0]))."&nbsp;games)</span>";
	// achievements
	$achi = "";
	$achiIndex = 0;
	$achRes = mysql_query("SELECT achievements.type,title,text FROM achievements,achievement_texts WHERE bot_id='".$l['id']."' AND achievements.type=achievement_texts.type ORDER BY datetime DESC;");
	while ($a = mysql_fetch_assoc($achRes)) {
		$achiIndex += 1;
		if ($achiIndex <= 2) {
			$classStr = "achievement_icon";
		} else {
			$classStr = "achievement_icon achievement_icon_small";
		}
		$achi .= '<a title="'.$a['title'].' ('.lcfirst(trim($a['text'],".")).')" href="./index.php?action=achievements#'.$a['type'].'"><img class="'.$classStr.'" src="./images/achievements/small/'.$a['type'].'.png" alt="'.$a['title'].'. " /></a>';
	}
	echo "<tr id=\"".urlencode($name)."\"><td>$name<a name=\"".urlencode($name)."\"></a></td><td><img alt=\"$race\" src=\"./images/$race.png\" /></td><td>$school</td><td>$desc</td><td>$status</td><td>$winRate</td><td>$draws[0]</td><td>$achi</td></tr>";
}
?>
</tbody>
</table>


<h2>Results (last 100 games)</h2>
<div>
<table id="resultlist">
<tr><td>Host</td><td>Guest</td><td>Result</td></tr>
<?php
	$res = mysql_query("SELECT bot1,bot2,result FROM games WHERE result='1' OR result='2' OR result='draw' ORDER BY datetime DESC LIMIT 100;");
	while ($line = mysql_fetch_assoc($res)) {
		$resBot1 = mysql_query("SELECT name,race FROM fos_user WHERE id='".$line['bot1']."' LIMIT 1");
		$resBot2 = mysql_query("SELECT name,race FROM fos_user WHERE id='".$line['bot2']."' LIMIT 1");
		$bot1 = mysql_fetch_assoc($resBot1);
		$bot2 = mysql_fetch_assoc($resBot2);
		echo '<tr><td>'.$bot1['name'].' ('.$bot1['race'].')</td><td>'.$bot2['name'].' ('.$bot2['race'].')</td><td>'.$line['result'].'</td></tr>';
	}
?>
</table>
</div>

