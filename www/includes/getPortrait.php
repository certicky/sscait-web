<?php

function getPortrait($race, $achRes, $asLink = true)
{
    $n = mysql_num_rows($achRes);
    if (in_array($n, [0, 1, 2])) {
        $id = 1;
    } elseif (in_array($n, [3, 4, 5])) {
        $id = 2;
    } elseif (in_array($n, [6, 7, 8])) {
        $id = 3;
    } elseif (in_array($n, [9, 10, 11])) {
        $id = 4;
    } elseif (in_array($n, [12, 13])) {
        $id = 5;
    } elseif (in_array($n, [14, 15])) {
        $id = 6;
    } elseif (in_array($n, [16, 17])) {
        $id = 7;
    } elseif (in_array($n, [18, 19])) {
        $id = 8;
    } elseif (in_array($n, [20, 21])) {
        $id = 9;
    } else {
        $id = 10;
    }

    $getRaceIndexCallback = function ($race) {
        if ($race == "Zerg") {
            return 'z';
        }

        if ($race == "Protoss") {
            return 'p';
        }

        if ($race == "Terran") {
            return 't';
        }

        return 'r';
    };
    
    // exception: we only have one "Random" image, so don't use $id there 
    if ($race == 'Random') $id = '';

    return ($asLink ? '<a href="./index.php?action=achievements">' : '')
           . '<img class="portrait_big" src="./images/portraits/'
           . $getRaceIndexCallback($race)
           . $id . '.gif" alt="" />'
           . ($asLink ? '</a>' : '');
}

function getSmallPortrait($id, $race)
{
    $res = mysql_query("SELECT count(if (bot_id='$id',1,NULL)) as count FROM `achievements` LIMIT 1");
	$line = mysql_fetch_assoc($res);
	$n = $line['count'];
        if (in_array($n,array(0,1,2))) {
                $id = 1;
        } elseif (in_array($n,array(3,4,5))) {
                $id = 2;
        } elseif (in_array($n,array(6,7,8))) {
                $id = 3;
        } elseif (in_array($n,array(9,10,11))) {
                $id = 4;
        } elseif (in_array($n,array(12,13))) {
                $id = 5;
        } elseif (in_array($n,array(14,15))) {
                $id = 6;
        } elseif (in_array($n,array(16,17))) {
                $id = 7;
        } elseif (in_array($n,array(18,19))) {
                $id = 8;
        } elseif (in_array($n,array(20,21))) {
                $id = 9;
        } else {
                $id = 10;
        }
        if ($race == "Zerg") {
                return '<img class="portrait_small" src="./images/portraits/small/z'.$id.'.gif" alt="" />';
        } elseif ($race == "Protoss") {
                return '<img class="portrait_small" src="./images/portraits/small/p'.$id.'.gif" alt="" />';
        } elseif ($race == "Terran") {
                return '<img class="portrait_small" src="./images/portraits/small/t'.$id.'.gif" alt="" />';
        } else {
                return '<img class="portrait_small" src="./images/portraits/small/r.gif" alt="" />';
        }

}

?>
