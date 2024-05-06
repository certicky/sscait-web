<?php

if ($GLOBALS["eliminationBracketPhase"]) {
    echo '<p style="text-align: center;">
    We\'re now resolving the elimination bracket.
    The results will become accessible again as soon as we\'re finished.
    </p>';
    die();
}

if ($GLOBALS["competitivePhase"] == false) echo '<p style="text-align: center;">
Bots and scores can now (during uncompetitive phase) be accessed at <a href="http://sscaitournament.com/index.php?action=scores">this page</a>.
</p>
<script>
window.location=\'http://sscaitournament.com/index.php?action=scores\';
</script>
';

include("./includes/getReplayURL.php");
include("./includes/getPortrait.php");

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
?>
<div id="upcomingMatchesWrapper" style="width: 100%; padding-bottom: 20px;">
<?php
$res = mysql_query("SELECT * FROM games WHERE result='unfinished' ORDER BY game_id ASC LIMIT 10;");
if (mysql_num_rows($res) > 0) {
	echo '<h2>Upcoming Matches:</h2><div style="font-size: 90%">';
	$matches = array();
	while ($l = mysql_fetch_assoc($res)) {
	        $res1 = mysql_query("SELECT full_name FROM fos_user WHERE id='".$l['bot1']."';");
	        $res2 = mysql_query("SELECT full_name FROM fos_user WHERE id='".$l['bot2']."';");
	        $n1 = mysql_fetch_assoc($res1);
	        $n2 = mysql_fetch_assoc($res2);
		$matches[] = '<span style="font-family: monospace;">'.$n1['full_name'].' vs. '.$n2['full_name'].'</span>';
	}
	if (mysql_num_rows($res) > 0)
		echo join(' <span class="match_divider">|</span> ',$matches);
	echo '</div>';
}
?>
</div>


<?php
// Get all the confirmed bots
//list($eloRatings, $iccupFormula, $iccupRanks, $eloRatingsNote) = getEloRatings();
$bots = array();
$res = mysql_query("SELECT * FROM fos_user WHERE bot_enabled=1 AND email_confirmed='1';");
while ($l = mysql_fetch_assoc($res)) {
	$name = $l['full_name'];
	$school = $l['school'];
	$student = $l['student'];
	$race = $l['bot_race'];
	//$desc =  strlen($l['bot_description']) > 120 ? substr($l['bot_description'],0,120)."..." : $l['bot_description'];
	$desc = $l['bot_description'];
	$update = $l['last_update_time'];
	if ($l['bot_enabled'] == '1') {
		$status = '<span class="ready">Enabled</span>';
	} else {
		$status = '<span class="disabled">Disabled</span>';
	}
	$winRes =  mysql_query("SELECT count(game_id) FROM `games` WHERE ((bot1='".$l['id']."' AND result='1') or (bot2='".$l['id']."' AND result='2'))");
	$lossRes = mysql_query("SELECT count(game_id) FROM `games` WHERE ((bot1='".$l['id']."' AND result='2') or (bot2='".$l['id']."' AND result='1'))");
	$drawsRes = mysql_query("SELECT count(game_id) FROM `games` WHERE  result='draw' AND ((bot1='".$l['id']."') OR (bot2='".$l['id']."'))");
	$wins = mysql_fetch_row($winRes);
	$losses = mysql_fetch_row($lossRes);
	$draws = mysql_fetch_row($drawsRes);
    if ($wins[0]+$losses[0] != 0) {
		$winRate = round(($wins[0]/($wins[0]+$losses[0]))*100,2);
	} else {
		$winRate = 0;
	}
	$score = $wins[0]*1+$draws[0];
	if (stripos($name,"(example)") !== FALSE) $score = "<span class=\"uncompetitive\">$score<br/>(uncompet.)</span>";
	if ($wins[0]+$losses[0]+$draws[0] != 0) {
		$avgScore = round(($wins[0]*3+$draws[0]*1)/(($wins[0]+$losses[0]+$draws[0])*3)*3,2);
	} else {
		$avgScore = 0;
	}
	// division
	if ($student == 1) {
		$division = "<b>Student</b>";
		if (trim($school) != "") $division .= ": $school";
	} else {
		$division = "<b>Mixed</b>";
	}
	// achievements
	$achi = "";
	$achiIndex = 0;
	$achRes = mysql_query("SELECT achievements.type,title,text FROM achievements,achievement_texts WHERE bot_id='".$l['id']."' AND achievements.type=achievement_texts.type ORDER BY datetime DESC;");
	while ($a = mysql_fetch_assoc($achRes)) {
		$achiIndex += 1;
		if ($achiIndex <= 6) {
		    $achi .= '<a title="'.$a['title'].' ('.lcfirst(trim($a['text'],".")).')" href="./index.php?action=achievements#'.$a['type'].'"><img class="achievement_icon achievement_icon_small" src="./images/achievements/small/'.$a['type'].'.png" alt="'.$a['title'].'. " /></a>';
		}
	}
	// Insert the bot into array
	$sortKey = $winRate;
    $bots[$l['id']] = [
        'id'              => $l['id'],
        'name'            => $name,
        'portrait'        => getPortrait($race, $achRes, false),
        'race'            => $race,
        //'eloRating'       => !empty($eloRatings[$name]) ? $eloRatings[$name] : '-',
        //'iccupFormula'    => !empty($iccupFormula[$name]) ? $iccupFormula[$name] : '-',
        //'iccupRank'       => !empty($iccupRanks[$name]) ? $iccupRanks[$name] : '-',
        'wins'            => $wins[0],
        'losses'          => $losses[0],
        'draws'           => $draws[0],
        'score'           => $score,
        'avgScore'        => $avgScore,
        'winRate'         => $winRate,
        'achievements'    => $achi,
        'achievementsNum' => mysql_num_rows($achRes),
        'division'        => $division,
        'status'          => $status,
        'description'     => $desc,
        'update'          => $update,
    ];
}

// sort the bot list
aasort($bots,"winRate");

/*
// ELO Ratings Chart
$minELOToRender = 2150;
$query                         = "SELECT * FROM historical_elo_ratings";
$statementHistoricalEloRatings = $pdo->prepare($query);
$statementHistoricalEloRatings->execute();

$historicalEloRatings = [];
$historicalEloRatingsDates = [];
foreach ($statementHistoricalEloRatings->fetchAll() as $row) {
    $id = $row['bot_id'];
    if (!isset($bots[$id])) {
        continue;
    }
    $date = $row['date'];
    if (!in_array($date, $historicalEloRatingsDates)) {
        $historicalEloRatingsDates[] = $date;
    }
    $eloRating = $row['elo_rating'];

    if (empty($eloRating)) {
        $eloRating = START_ELO;
    }
    $name = $bots[$id]['name'];
    if (!isset($historicalEloRatings[$date])) {
        $historicalEloRatings[$date] = [];
    }
    $historicalEloRatings[$date][$id] = $eloRating;
}

?>

<script type="text/javascript" src="./js/utils.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
    	if (detectSmallScreen()) {
    	    $('#eloRatingsChartDivWrapper').hide();
    	} else {
            var data = google.visualization.arrayToDataTable([
    			<?php

    				// chart: 1st row
    				echo "['Day', ";

    				$pieces = array();
    				foreach ($bots as $id => $bot) {
    					$pieces[] = "{label: '".$bot['name']."', type: 'number'}";
    				}
    				echo implode(", ",$pieces)."], \n";

    				// chart: remaining rows
    				foreach ($historicalEloRatings as $date => $eloRatingsForDate) {
    					echo "['".substr($date,5)."', ";

    					$chartsData = [];
    					foreach($bots as $id => &$bot) {
					        $chartsData[] = empty($eloRatingsForDate[$bot['id']]) ? START_ELO : $eloRatingsForDate[$bot['id']];
    					}
    					echo implode(", ",$chartsData)."], \n";
    				}
    			?>
            ]);

            var options = {
              curveType: 'function',
              lineWidth: 2,
              backgroundColor: 'rgb(240,240,240)',
              legend: { position: 'top' },
              chartArea: {width: '85%', height: '80%'},
            };

            // draw chart (all the bots)
            var chart = new google.visualization.LineChart(document.getElementById('eloRatingsChartDiv'));
            chart.draw(data, options);

            var columns = [];
            var series = {};
            for (var i = 0; i < data.getNumberOfColumns(); i++) {
                columns.push(i);
                if (i > 0) {
					series[i-1] = {};
                }
            }

            // hide / show certain bots when their nema is clicked in the legend
            google.visualization.events.addListener(chart, 'select', function () {

                var sel = chart.getSelection();
                // if selection length is 0, we deselected an element
                if (sel.length > 0) {
                    // if row is undefined, we clicked on the legend
                    if (sel[0].row === null) {
                        var col = sel[0].column;
                        if (columns[col] == col) {
                            // hide the data series
                            columns[col] = {
                                label: data.getColumnLabel(col),
                                type: data.getColumnType(col),
                                calc: function () {
                                    return null;
                                }
                            };
                        }
                        else {
                            // show the data series
                            columns[col] = col;
                            series[col - 1].color = null;
                        }
                        var view = new google.visualization.DataView(data);
                        view.setColumns(columns);
                        chart.draw(view, options);
                    }
                }
            });

            // auto-hide bots below the ELO threshold
            for (var i = 0; i < data.getNumberOfColumns(); i++) {

            	if (data.getColumnRange(i).max < <?php echo $minELOToRender; ?>) {
            		var col = columns[i];
                	// hide the data series
                    columns[col] = {
                        label: data.getColumnLabel(col),
                        type: data.getColumnType(col),
                        calc: function () {
                            return null;
                        }
                    };
            	}
            }
            var view = new google.visualization.DataView(data);
            view.setColumns(columns);
            chart.draw(view, options);

    	}
      }

</script>
<div id="eloRatingsChartDivWrapper">
	<h2>ELO Ratings over Time</h2>
	<div id="eloRatingsChartDiv" style="width: auto; height: 220px; "></div>
    <div style="margin: 0 auto; text-align: center; color: rgb(40,40,40); font-size: 80%; padding-top: 10px; padding-bottom: 10px;">
    This chart displays the ELO rating over time. By default, only the bots with ELO higher than <?php echo $minELOToRender;?> are displayed. Add more bots to the chart by clicking their name in the legend.
    </div>
</div>

<?php */ ?>

<h2>Bots and Score:</h2>
<table id="bot_list" class="sortable bot_stats stickyHeader">
<thead>
    <tr>
        <th class="td_name">Name</th>
        <th class="td_race">Race</th>
        <!-- <th class="td_elo">Elo Rating*</th> -->
        <!-- <th class="td_iccup_formula">ICCUP Formula*</th>  -->
        <!-- <th class="td_iccup_ank">SSCAIT Rank*</th> -->
        <th class="td_win_rate">Win Rate</th>
        <th class="td_wins">Score (wins)</th>
        <th class="td_losses">Losses</th>
        <!-- <th class="td_draws">Draws</th> -->
        <th class="td_description">Description</th>
        <!-- <th class="td_achievements">Latest Achievements</th> -->
        <th class="td_division">Division</th>
        <!-- <th class="td_status">Status</th>  -->
        <th class="td_updated">Updated</th>
    </tr>
</thead>
<tbody>
<?php

// Print it all out
foreach ($bots as $bot) {
	if (is_numeric($bot['winRate'])) {
		$winRateText = $bot['winRate']."%";
	} else {
		$winRateText = '<span style="font-size: 80%">'.$bot['winRate'].'</span>';
	}
    echo "<tr id=\""
         . urlencode($bot['name'])
         . "\"><td class=\"name_portrait\"><a href=\"./index.php?action=botDetails&amp;bot=".urlencode($bot['name'])."\">"
         . $bot['portrait']
         . "</a><br/><a href=\"./index.php?action=botDetails&amp;bot=".urlencode($bot['name'])."\">"
         . $bot['name']
         . "</a><a name=\""
         . urlencode($bot['name'])
         . "\"></a></td><td><span class=\"invisible\">"
         . $bot['race']
         . "</span><img alt=\"\" src=\"./images/"
         . $bot['race']
         . ".png\" title=\""
         . $bot['race']
         . "\" /></td>"
         //. "<td class=\"wins\">" . $bot['eloRating'] . "</td>"
         //. "<td class=\"wins\">" . $bot['iccupFormula'] . "</td>"
         //. "<td class=\"wins\">" . $bot['iccupRank'] . "</td>"
         . "<td>"
         . $winRateText
         . "</td><td class=\"wins\">"
         . $bot['wins']
         . "</td><td class=\"losses\">"
         . $bot['losses']
         . "</td>"
         //. "<td class=\"draws\">"
         //. $bot['draws']
         //. "</td>"
         ."<td class=\"bot_description\">"
         . preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $bot['description'])
         . "</td>"
         //. "<td><span class=\"invisible\">"
         //. $bot['achievementsNum']
         //. ": </span>"
         //. $bot['achievements']
         //. "</td>"
         ."<td class=\"bot_division\" style=\"font-size: 85%\">"
         . $bot['division']
         . "</td>"
         //. "<td>"
         //. $bot['status']
         //. "</td>"
         . "<td class=\"bot_updated\" style=\"font-size: 85%;\">"
         . $bot['update']
         //. "<div class=\"binary-link\">[ <a target=\"_blank\" href=\"./bot_binary.php?bot="
         //. urlencode($bot['name'])
         //. "\">binary</a> ]<br/>[ <a target=\"_blank\" href=\"./bot_binary.php?bot="
         //. urlencode($bot['name'])
         //. "&amp;bwapi_dll=true\">bwapi.dll</a> ]"
		 ."</div></td></tr>\n";
}

?>
</tbody>
</table>

<script>
	// Go to Bot profile page when user clicks anywhere on <td>
	$('#bot_list td').click(function() {
		window.location.href = $(this).parent().find('td.name_portrait a').attr('href');
	});
</script>

<a name="results"></a>
<h2>Game Results:</h2>
<div>
<table id="resultlist">
<tr><td>Bot 1</td><td>Bot 2</td><td>Result</td><td>Notes</td><td>Time</td><td>Replay</td></tr>
<?php
	$res = mysql_query("SELECT game_id,bot1,bot2,result,datetime,note FROM games WHERE result='1' OR result='2' OR result='draw' ORDER BY datetime DESC LIMIT 200;");
	while ($line = mysql_fetch_assoc($res)) {
		$resBot1 = mysql_query("SELECT full_name,bot_race,bot_type FROM fos_user WHERE id='".$line['bot1']."' LIMIT 1");
		$resBot2 = mysql_query("SELECT full_name,bot_race,bot_type FROM fos_user WHERE id='".$line['bot2']."' LIMIT 1");
		$bot1 = mysql_fetch_assoc($resBot1);
		$bot2 = mysql_fetch_assoc($resBot2);
		echo '<tr>';
		echo '<td><a href="./index.php?action=botDetails&bot='.urlencode($bot1['full_name']).'">'.$bot1['full_name'].'</a><br/>('.$bot1['bot_race'].', '.$bot1['bot_type'].')</td><td><a href="./index.php?action=botDetails&bot='.urlencode($bot2['full_name']).'">'.$bot2['full_name'].'</a><br/>('.$bot2['bot_race'].', '.$bot2['bot_type'].')</td><td>Bot '.$line['result'].'</td><td>'.str_replace(";"," ",str_replace("draw","timeOut",str_replace("_crashed","crashed",$line['note']))).'</td><td>'.date("Y-m-d H:i:s",$line['datetime']).'</td>';
		$repUrl = getReplayFileURL($line['game_id'], $bot1['full_name'], $bot2['full_name']);
		if ($repUrl != '') {
		    echo '<td><a href="'.$repUrl.'" target="_blank">.rep</a> / <a href="http://www.openbw.com/replay-viewer/?rep='.urlencode($repUrl).'" target="_blank">watch</a></td>';
		} else {
		    echo '<td></td>';
		}
		echo '</tr>';
	}
?>
</table>
</div>

<?php /*
if (!empty($eloRatingsRemoteURL)) {
    ?>
    <hr>
    <div>*Note: <?php echo($eloRatingsNote); ?></div>
    <?php
}
*/ ?>
