<?php
// Allowed API parameters:
// 'bots': (optional) Comma-delimited list of bot names. If passed, API will only return the games of these bots.
// 'future': (optional; default=true) If future=true, the API returns only future planned games. If future=false, only past games.
// 'count': (optional; default=10) Number of games per page to return.
// 'page': (optional; default=1) Which game page to return.

header('Content-Type: application/json');

// first, check our cache, before we do anything with the DB
include '../includes/generalFunctions.php';
$cache = new SimpleCache();
$key = 'api_games_';
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

// get MySQL-sanitized version of all the GET parameters
$get = array();
foreach ($_GET as $k => $v) {
	$get[$k] = mysql_real_escape_string($v,$GLOBALS['mysqlConnection']);
}

function getName($id) {
	$res = mysql_query("SELECT full_name FROM fos_user WHERE id='$id'");
	if (mysql_num_rows($res) > 0) {
		$l = mysql_fetch_assoc($res);
		return $l['full_name'];
	} else return "";
}

function getBotId($name) {
	$res = mysql_query("SELECT id FROM fos_user WHERE full_name='".urldecode($name)."'");
	if (mysql_num_rows($res) > 0) {
		$l = mysql_fetch_assoc($res);
		return $l['id'];
	} else return "";
}

function getReplayLink($gameId, $bot1Name, $bot2Name) {
	$paddedGameId = str_pad($gameId, 4, '0', STR_PAD_LEFT);

	foreach (scandir($GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"].'/'.strtoupper($bot1Name)) as $fname) {
		if (strpos($fname, $paddedGameId.'-') === 0) return $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/Replays/'.strtoupper($bot1Name).'/'.$fname;
	}
	foreach (scandir($GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"].'/'.strtoupper($bot2Name)) as $fname) {
		if (strpos($fname, $paddedGameId.'-') === 0) return $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/Replays/'.strtoupper($bot2Name).'/'.$fname;
	}
	return null;
}

if (isset($get['page']) && is_numeric($get['page'])) {
        $page = $get['page'];
} else {
        $page = 1;
}
if (isset($get['count']) && is_numeric($get['count'])) {
        $count = min(1000,$get['count']);
} else {
        $count = 10;
}
$futureConditions = "";
if (isset($get['future']) && (strtolower($get['future']) == "false" || $get['future'] == "0")) $futureConditions = "(result != 'unfinished' AND result != 'error')";
if ((!isset($get['future'])) || ((isset($get['future']) && (strtolower($get['future']) == "true" || $get['future'] == "1")))) $futureConditions = "(result = 'unfinished' OR result = 'error')";

if (isset($get['bots'])) {
	$botStr = mysql_real_escape_string($get['bots']);
	$botConds = array();
	foreach (explode(",",$botStr) as $bot) {
		$botConds[] = "((bot1='".getBotId($bot)."') OR (bot2='".getBotId($bot)."'))";
	}
	if (sizeof($botConds) > 0) {
		$botConditions = "(".join(" OR ",$botConds).")";
		if ($futureConditions != "") $botConditions = "AND ".$botConditions;
	} else {
		$botConditions = "";
	}
} else {
	$botConditions = "";
}

$resultStrings = array();
if ($GLOBALS["eliminationBracketPhase"] == false) {
    $res = mysql_query("SELECT datetime,bot1,bot2,result,map,note,game_id FROM games WHERE $futureConditions $botConditions  ORDER BY game_id DESC LIMIT ".($count*($page-1)).",".($count).";");
    while ($n = mysql_fetch_assoc($res)) {
        $n['timestamp'] = $n['datetime'];
        $n['host'] = getName($n['bot1']);
        $n['guest'] = getName($n['bot2']);
        
        $n['replay'] = getReplayLink($n['game_id'],$n['host'],$n['guest']);
        
        unset($n['datetime']);
        unset($n['bot1']);
        unset($n['bot2']);
        unset($n['game_id']);
        array_push($resultStrings, json_encode($n,JSON_PRETTY_PRINT));
    }
}

$out = "[\n".implode(",\n",$resultStrings)."\n]";
$cache->set($key, $out);
echo $out;

?>
