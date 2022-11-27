<?php
// Allowed API parameters:
// 'bot': (optional) Full name of the bot to return. If not passed, API returns all the bots.

header('Content-Type: application/json');

// first, check our cache, before we do anything with the DB
include '../includes/generalFunctions.php';
$cache = new SimpleCache();
$key = 'api_bots_';
foreach ($_GET as $k => $v) {
	$key .= preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $k) . '_' . preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $v);
}

$contentsStr = $cache->get($key, 60*4); // 4 minutes allowed cache age
if ($contentsStr != null) {
	echo $contentsStr;
	exit();
}


//===============================================
// DB connection & other settings
//===============================================
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';
require_once '../settings_server.php';
include('../includes/getPortrait.php');

// get MySQL-sanitized version of all the GET parameters
$get = array();
foreach ($_GET as $k => $v) {
	$get[$k] = mysql_real_escape_string($v,$GLOBALS['mysqlConnection']);
}

// Function used to sort the array of bots by a certain key
function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    arsort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

// Return only one bot if 'bot' parameter is passed
if (isset($get['bot'])) {
	$botCond = "AND full_name='".urldecode($get['bot'])."'";
} else {
	$botCond = "";
}

// Get all the confirmed bots
$bots = array();
$res = mysql_query("SELECT * FROM fos_user WHERE email_confirmed='1' $botCond;");
while ($l = mysql_fetch_assoc($res)) {
	$name = $l['full_name'];
	$school = $l['school'];
	$student = $l['student'];
	$race = $l['bot_race'];
	$desc = $l['bot_description'];
	$update = $l['last_update_time'];
	$type = $l['bot_type'];
	if ($l['bot_enabled'] == '1') {
		$status = 'Enabled';
	} else {
		$status = 'Disabled';
	}
	
	if ($GLOBALS["eliminationBracketPhase"] == false) { // hide the results during elimination bracket phase
    	$winRes =  mysql_query("SELECT count(game_id) FROM `games` WHERE ((bot1='".$l['id']."' AND result='1') or (bot2='".$l['id']."' AND result='2'))");
    	$lossRes = mysql_query("SELECT count(game_id) FROM `games` WHERE ((bot1='".$l['id']."' AND result='2') or (bot2='".$l['id']."' AND result='1'))");
    	$drawsRes = mysql_query("SELECT count(game_id) FROM `games` WHERE  result='draw' AND ((bot1='".$l['id']."') OR (bot2='".$l['id']."'))");
    	$wins = mysql_fetch_row($winRes);
    	$losses = mysql_fetch_row($lossRes);
    	$draws = mysql_fetch_row($drawsRes);
    	$winRecentRes =  mysql_query("SELECT count(game_id) FROM `games` WHERE (((bot1='".$l['id']."' AND result='1') or (bot2='".$l['id']."' AND result='2')) AND (datetime > (SELECT datetime FROM games WHERE (bot1='".$l['id']."' OR bot2='".$l['id']."') AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT ".$recentGames.",1)  ))");
    	$lossRecentRes = mysql_query("SELECT count(game_id) FROM `games` WHERE (((bot1='".$l['id']."' AND result='2') or (bot2='".$l['id']."' AND result='1')) AND (datetime > (SELECT datetime FROM games WHERE (bot1='".$l['id']."' OR bot2='".$l['id']."') AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT ".($recentGames).",1) ))");
    	$drawsRecentRes = mysql_query("SELECT count(game_id) FROM `games` WHERE  (result='draw' AND ((bot1='".$l['id']."') OR (bot2='".$l['id']."')) AND (datetime > (SELECT datetime FROM games WHERE (bot1='".$l['id']."' OR bot2='".$l['id']."') AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT ".$recentGames.",1)  ) )");
    	$winsRecent = mysql_fetch_row($winRecentRes);
    	$lossesRecent = mysql_fetch_row($lossRecentRes);
    	$drawsRecent = mysql_fetch_row($drawsRecentRes);
	} else {
	    $wins = array(0); $losses = array(0); $draws = array(0);
	    $winsRecent = array(0); $lossesRecent = array(0); $drawsRecent = array(0);
	}
    
    if ($winsRecent[0]+$lossesRecent[0] != 0) {
		$winRate = round(($winsRecent[0]/($winsRecent[0]+$lossesRecent[0]))*100,2);
	} else {
		$winRate = 0;
	}
	if ($wins[0]+$losses[0]+$draws[0] <= $recentGames) $winRate = "not enough games";
	$score = $wins[0]*3+$draws[0];
	if (stripos($name,"(example)") !== FALSE) $score = "<span class=\"uncompetitive\">$score<br/>(uncompet.)</span>";
	if ($wins[0]+$losses[0]+$draws[0] != 0) {
		$avgScore = round(($wins[0]*3+$draws[0]*1)/(($wins[0]+$losses[0]+$draws[0])*3)*3,2);
	} else {
		$avgScore = 0;
	}
	// division
	if ($student == 1) {
		$division = "Student";
		if (trim($school) != "") $division .= ": $school";
	} else {
		$division = "Mixed";
	}
	// achievements
	$achi = array();
	$achiIndex = 0;
	$achRes = mysql_query("SELECT achievements.type,title,text FROM achievements,achievement_texts WHERE bot_id='".$l['id']."' AND achievements.type=achievement_texts.type ORDER BY datetime DESC;");
	while ($a = mysql_fetch_assoc($achRes)) {
		$achiIndex += 1;
		if ($achiIndex <= 2) {
			$classStr = "achievement_icon";
		} else {
			$classStr = "achievement_icon achievement_icon_small";
		}
		$achi[] = $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/images/achievements/'.$a['type'].'.png';
	}
	$port = getPortrait($race,$achRes); 
	$portSpl = explode('src=".',$port);
	$portSpl = explode('"',$portSpl[1]);
	$port = $GLOBALS["DOMAIN_WITHOUT_SLASH"].$portSpl[0];
	
	$binary = $GLOBALS["DOMAIN_WITHOUT_SLASH"]."/bot_binary.php?bot=".urlencode($name);
	$bwapiDll = $GLOBALS["DOMAIN_WITHOUT_SLASH"]."/bot_binary.php?bot=".urlencode($name)."&bwapi_dll=true";
	
	$profileURL = $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/index.php?action=botDetails&bot='.urlencode($name);
	
	// Insert the bot into array
	$sortKey = $winRate;
	$bots[] = array('name'=>$name,'portrait'=>$port,'race'=>$race,'wins'=>$wins[0],'losses'=>$losses[0],'draws'=>$draws[0],'score'=>$score,'avgScore'=>$avgScore,'winRate'=>$winRate,'achievements'=>$achi,'achievementsNum'=>mysql_num_rows($achRes),'division'=>$division,'status'=>$status,'description'=>$desc,'update'=>$update, 'botBinary'=>$binary, 'bwapiDLL'=>$bwapiDll, 'botType'=>$type, 'botProfileURL'=>$profileURL);
}

// Print it all out and save the cache
aasort($bots,"winRate"); // but sort it first
$resultStrings = array();
foreach ($bots as $bot) {
	if (is_numeric($bot['winRate'])) {
		$winRateText = $bot['winRate']."%";
	} else {
		$winRateText = '<span style="font-size: 80%">'.$bot['winRate'].'</span>';
	}
	$resultStrings[] = json_encode($bot, JSON_PRETTY_PRINT);
}

$out = "[\n".implode(",\n",$resultStrings)."\n]";
$cache->set($key, $out);
echo $out;


?>
