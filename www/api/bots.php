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
if (isset($_GET['bot'])) {
	$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a", ";");
	$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z", "");
	$botsStr = str_replace($search, $replace, urldecode($_GET['bot']));
	$botCond = "AND full_name='".$botsStr."'";
} else {
	$botCond = "";
}

// Get all the confirmed bots
$bots = array();
$stmt = $GLOBALS['mysqliConnection']->prepare("SELECT * FROM fos_user WHERE email_confirmed=:emailConfirmed $botCond;");
$stmt->bindValue(':emailConfirmed', '1', PDO::PARAM_STR);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $l) {
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
	    $stmt = $GLOBALS['mysqliConnection']->prepare("
	    	SELECT
			    (SELECT count(game_id) FROM `games` WHERE (bot1=:id AND result='1') or (bot2=:id AND result='2')) as wins,
			    (SELECT count(game_id) FROM `games` WHERE (bot1=:id AND result='2') or (bot2=:id AND result='1')) as losses,
			    (SELECT count(game_id) FROM `games` WHERE result='draw' AND (bot1=:id OR bot2=:id)) as draws,
			    (SELECT count(game_id) FROM `games` WHERE
			    	((bot1=:id AND result='1') or (bot2=:id AND result='2')) AND
			    	(datetime > (SELECT datetime FROM games WHERE (bot1=:id OR bot2=:id) AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT 1)  ))
			    		as winsRecent,
			    (SELECT count(game_id) FROM `games` WHERE
			    	((bot1=:id AND result='2') or (bot2=:id AND result='1')) AND
			    	(datetime > (SELECT datetime FROM games WHERE (bot1=:id OR bot2=:id) AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT 1) ))
			    		as lossesRecent,
			    (SELECT count(game_id) FROM `games` WHERE
			    	(result='draw' AND (bot1=:id OR bot2=:id)) AND
			    	(datetime > (SELECT datetime FROM games WHERE (bot1=:id OR bot2=:id) AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT 1) ))
			    		as drawsRecent
			FROM `games`");
		$stmt->execute(array(':id' => $l['id']));
		$result = $stmt->fetch();

		$wins = $result['wins'];
		$losses = $result['losses'];
		$draws = $result['draws'];
		$winsRecent = $result['winsRecent'];
		$lossesRecent = $result['lossesRecent'];
		$drawsRecent = $result['drawsRecent'];

	} else {
	    $wins = 0; $losses = 0; $draws = 0;
	    $winsRecent = 0; $lossesRecent = 0; $drawsRecent = 0;
	}
    
    if ($winsRecent+$lossesRecent != 0) {
		$winRate = round(($winsRecent/($winsRecent+$lossesRecent))*100,2);
	} else {
		$winRate = 0;
	}
	if ($wins+$losses+$draws <= 10) $winRate = "not enough games";
	$score = $wins*3+$draws;
	if (stripos($name,"(example)") !== FALSE) $score = "<span class=\"uncompetitive\">$score<br/>(uncompet.)</span>";
	if ($wins+$losses+$draws != 0) {
		$avgScore = round(($wins*3+$draws*1)/(($wins+$losses+$draws)*3)*3,2);
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
	$stmt = $GLOBALS['mysqliConnection']->prepare("SELECT achievements.type,title,text FROM achievements,achievement_texts WHERE bot_id=:botId AND achievements.type=achievement_texts.type ORDER BY datetime DESC;");
	$stmt->bindValue(':botId', $l['id'], PDO::PARAM_INT);
	$stmt->execute();
	$achRes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$achiIndex = 0;
	foreach ($achRes as $a) {
	    if ($achiIndex <= 2) {
	        $classStr = "achievement_icon";
	    } else {
	        $classStr = "achievement_icon achievement_icon_small";
	    }
	    $achi[] = $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/images/achievements/'.$a['type'].'.png';
	    $achiIndex++;
	}

	$port = getPortrait($race,$achRes);
	$portSpl = explode('src=".',$port);
	$portSpl = explode('"',$portSpl[1]);
	$port = $GLOBALS["DOMAIN_WITHOUT_SLASH"].$portSpl;

	$binary = $GLOBALS["DOMAIN_WITHOUT_SLASH"]."/bot_binary.php?bot=".urlencode($name);
	$bwapiDll = $GLOBALS["DOMAIN_WITHOUT_SLASH"]."/bot_binary.php?bot=".urlencode($name)."&bwapi_dll=true";
	$profileURL = $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/index.php?action=botDetails&bot='.urlencode($name);
	$achievementsNum = count($achRes);

	// Insert the bot into array
	$sortKey = $winRate;
	$bots[] = array('name'=>$name,'portrait'=>$port,'race'=>$race,'wins'=>$wins,'losses'=>$losses,'draws'=>$draws,'score'=>$score,'avgScore'=>$avgScore,'winRate'=>$winRate,'achievements'=>$achi,'achievementsNum'=>$achievementsNum,'division'=>$division,'status'=>$status,'description'=>$desc,'update'=>$update, 'botBinary'=>$binary, 'bwapiDLL'=>$bwapiDll, 'botType'=>$type, 'botProfileURL'=>$profileURL);
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
