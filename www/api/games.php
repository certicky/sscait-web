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

function getName($id) {
    $stmt = $GLOBALS['mysqliConnection']->prepare("SELECT full_name FROM fos_user WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['full_name'];
    } else {
        return "";
    }
}

function getBotId($name) {
    $stmt = $GLOBALS['mysqliConnection']->prepare("SELECT id FROM fos_user WHERE full_name = :name");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id'];
    } else {
        return "";
    }
}

function getReplayLink($gameId, $bot1Name, $bot2Name) {
	$paddedGameId = str_pad($gameId, 4, '0', STR_PAD_LEFT);

	$bot1Dir = $GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"].'/'.strtoupper($bot1Name);
	if (is_dir($bot1Dir) && is_readable($bot1Dir)) {
		foreach (scandir($bot1Dir) as $fname) {
			if (strpos($fname, $paddedGameId.'-') === 0) return $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/Replays/'.strtoupper($bot1Name).'/'.$fname;
		}
	}
	$bot2Dir = $GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"].'/'.strtoupper($bot2Name);
	if (is_dir($bot2Dir) && is_readable($bot2Dir)) {
		foreach (scandir($bot2Dir) as $fname) {
			if (strpos($fname, $paddedGameId.'-') === 0) return $GLOBALS["DOMAIN_WITHOUT_SLASH"].'/Replays/'.strtoupper($bot2Name).'/'.$fname;
		}
	}
	return null;
}

if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $page = $_GET['page'];
} else {
        $page = 1;
}
if (isset($_GET['count']) && is_numeric($_GET['count'])) {
        $count = min(1000,$_GET['count']);
} else {
        $count = 10;
}
$futureConditions = "";
if (isset($_GET['future']) && (strtolower($_GET['future']) == "false" || $_GET['future'] == "0")) $futureConditions = "(result != 'unfinished' AND result != 'error')";
if ((!isset($_GET['future'])) || ((isset($_GET['future']) && (strtolower($_GET['future']) == "true" || $_GET['future'] == "1")))) $futureConditions = "(result = 'unfinished' OR result = 'error')";

if (isset($_GET['bots'])) {
	$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a", ";");
    	$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z", "");
    	$botStr = str_replace($search, $replace, $_GET['bots']);
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
    $stmt = $GLOBALS['mysqliConnection']->prepare("SELECT datetime,bot1,bot2,result,map,note,game_id FROM games WHERE $futureConditions $botConditions  ORDER BY game_id DESC LIMIT :start,:count");
    $stmt->bindValue(':start', ($count*($page-1)), PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $stmt->execute();
    while ($n = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
