<?php 

// return the URL of a replay file corresponding to a specific game
function getReplayFileURL($gameId, $bot1name, $bot2name) {
    $baseReplaysPath = $GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"];
    $gameId = str_pad($gameId, 4, "0", STR_PAD_LEFT);
    
    // try to find the replay file in bot bots' folders
    $files = array();
    foreach (glob(rtrim($baseReplaysPath,"/").'/'.strtoupper($bot1name).'/'.$gameId.'-'.substr($bot1name,0,4).'_'.substr($bot2name,0,4).'-*.rep') as $match) {
        $files[] = $match;
    }
    if (sizeof($files) == 0) {
    	foreach (glob(rtrim($baseReplaysPath,"/").'/'.strtoupper($bot2name).'/'.$gameId.'-'.substr($bot2name,0,4).'_'.substr($bot1name,0,4).'-*.rep') as $match) {
            $files[] = $match;
        }
    }
    
    if (sizeof($files) != 0) {
        return str_ireplace($baseReplaysPath,"https://sscaitournament.com/Replays",$files[0]);
    } else {
        return "";
    }
    
}

?>