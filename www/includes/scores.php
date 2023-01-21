<?php

if ($GLOBALS["eliminationBracketPhase"]) {
    echo '<p style="text-align: center;">
    We\'re now resolving the elimination bracket.
    The results will become accessible again as soon as we\'re finished.
    </p>';
    die();
}

if ($GLOBALS["competitivePhase"] == true) echo '<p style="text-align: center;">
Bots and scores can now (during competitive phase) be accessed at <a href="http://sscaitournament.com/index.php?action=scoresCompetitive">this page</a>.
</p>
<script>
window.location=\'http://sscaitournament.com/index.php?action=scoresCompetitive\';
</script>
';

include("./includes/getReplayURL.php");

// cache settings
$cachePath = "/tmp/_cache_score.html";
// how many minutes is the cache relevant
$cacheRenewMinutes = 4;

// check if we have recent cached version of this page and return it if we do
if (empty($GLOBALS['debugMode'])
    && file_exists($cachePath)
) {
	if (time()-filemtime($cachePath) < $cacheRenewMinutes * 60) {
		readfile($cachePath);
		echo "<p>Contents read from cache from ".date("F d Y H:i:s", filemtime($cachePath)).".</p>";
		return;
	}
}

// start the buffering in order to save the cache
ob_start();

include("./includes/getPortrait.php");
$recentGames = 50;	// how many recent games should be considered for win ratio computation

// Function used to sort the array of bots by a certain key
function aasort(array &$bots, string $key) {
    usort($bots, function($a, $b) use ($key) {
        return $b[$key] <=> $a[$key];
    });
}

?>


<div id="upcomingMatchesWrapper">
    <?php
    $res = mysql_query("
        SELECT
            bot1.full_name as bot1_name,
            bot2.full_name as bot2_name
        FROM games
        LEFT JOIN fos_user bot1 ON bot1.id = games.bot1
        LEFT JOIN fos_user bot2 ON bot2.id = games.bot2
        WHERE result='unfinished'
        ORDER BY game_id ASC
        LIMIT 10;
    ");
        echo '<h2>Upcoming Matches:</h2><div style="font-size: 90%">';
        while ($match = mysql_fetch_assoc($res)) {
            echo '<span style="font-family: monospace;">'.$match['bot1_name'].' vs. '.$match['bot2_name'].'</span>';
            echo ' <span class="match_divider">|</span> ';
        }
        echo '</div>';
    ?>
    <div style="margin-top: 15px; font-size: 80%">
        <table>
        	<tr>
        		<td>To change the match schedule:</td>
        		<td><a class="button_link" href="index.php?action=voteForPlayers">Vote for Players</a></td>
        	</tr>
        	<tr>
        		<td>To see bot's ELO in more detail:</td>
        		<td><a class="button_link" href="index.php?action=eloChartBig">Show Big ELO Chart</a></td>
        	</tr>
        </table>
    </div>
</div>



<?php
list($eloRatings, $iccupFormula, $iccupRanks, $eloRatingsNote) = getEloRatings();

// Get all the confirmed bots
$bots = array();
$res = mysql_query("SELECT * FROM fos_user WHERE email_confirmed='1'");
while ($l = mysql_fetch_assoc($res)) {
    $name = $l['full_name'];
    $school = $l['school'];
    $student = $l['student'];
    $race = $l['bot_race'];
    $desc = $l['bot_description'];
    $update = $l['last_update_time'];
    if ($l['bot_enabled'] == '1') {
        $status = '<span class="ready">Enabled</span>';
    } else {
        $status = '<span class="disabled">Disabled</span>';
    }
    $gameRes = mysql_query("SELECT SUM(result = '1') as wins, SUM(result = '2') as losses, SUM(result = 'draw') as draws FROM games WHERE bot1 = '".$l['id']."' OR bot2 = '".$l['id']."'");
    $games = mysql_fetch_assoc($gameRes);
    $wins = $games['wins'];
    $losses = $games['losses'];
    $draws = $games['draws'];
    $recentRes = mysql_query("SELECT SUM(result = '1') as wins, SUM(result = '2') as losses, SUM(result = 'draw') as draws FROM games WHERE (bot1 = '".$l['id']."' OR bot2 = '".$l['id']."') AND datetime > (SELECT datetime FROM games WHERE (bot1='".$l['id']."' OR bot2='".$l['id']."') AND (result IN('1','2','draw')) ORDER BY datetime DESC LIMIT ".$recentGames.",1) ");
    $recent = mysql_fetch_assoc($recentRes);
    $winsRecent = $recent['wins'];
    $lossesRecent = $recent['losses'];
    $drawsRecent = $recent['draws'];
    if ($winsRecent+$lossesRecent != 0) {
        $winRate = round(($winsRecent/($winsRecent+$lossesRecent))*100,2);
    } else {
        $winRate = "N/A";
    }
    if ($wins+$losses+$draws != 0) {
        $avgScore = round(($wins*3+$draws*1)/(($wins+$losses+$draws)*3)*3,2);
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

    $bots[$l['id']] = array(
        'id' => $l['id'],
        'name' => $name,
        'portrait' => getPortrait($race, $achRes, false),
        'school' => $school,
        'student' => $student,
        'race' => $race,
        'eloRating' => !empty($eloRatings[$name]) && $l['bot_enabled'] == '1' ? $eloRatings[$name] : '-',
        'iccupFormula' => !empty($iccupFormula[$name]) ? $iccupFormula[$name] : '-',
        'iccupRank' => !empty($iccupRanks[$name]) ? $iccupRanks[$name] : '-',
        'description' => $desc,
        'update' => $update,
        'status' => $status,
        'wins' => $wins,
        'losses' => $losses,
        'draws' => $draws,
        'score' => ($wins*3+$draws),
        'avgScore' => $avgScore,
        'winsRecent' => $winsRecent,
        'lossesRecent' => $lossesRecent,
        'drawsRecent' => $drawsRecent,
        'winRate' => $winRate,
        'achievements' => $achi,
        'achievementsNum' => mysql_num_rows($achRes),
        'division' => $division
    );
}

// sort the bot list
aasort($bots,"eloRating");


// ELO Ratings Chart
$minELOToRender = 2150;
$query = "SELECT bot_id, date, elo_rating FROM historical_elo_ratings JOIN fos_user ON (bot_id=id) WHERE (date > (NOW() - INTERVAL 6 MONTH)) AND (bot_enabled=1) AND (WEEKDAY(date) = 0 OR WEEKDAY(date) = 3)";
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



<h2>Bots and Score:</h2>
<table id="bot_list" class="sortable bot_stats stickyHeader">
<thead>
    <tr>
        <th class="td_name">Name</th>
        <th class="td_race">Race</th>
        <th class="td_elo">Elo Rating*</th>
        <th class="td_iccup_formula">ICCUP Formula*</th>
        <th class="td_iccup_ank">SSCAIT Rank*</th>
        <th class="td_win_rate">Win Rate<br/><span style="font-size: 80%;">(last&nbsp;<?php echo $recentGames; ?>&nbsp;games)</span></th>
        <th class="td_wins">Wins</th>
        <th class="td_losses">Losses</th>
        <!-- <th class="td_draws">Draws</th> -->
        <th class="td_description">Description</th>
        <th class="td_achievements">Latest Achievements</th>
        <th class="td_division">Division</th>
        <th class="td_status">Status</th>
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
         . "<td class=\"wins\">" . $bot['eloRating'] . "</td>"
         . "<td class=\"wins\">" . $bot['iccupFormula'] . "</td>"
         . "<td class=\"wins\">" . $bot['iccupRank'] . "</td>"
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
         . "<td><span class=\"invisible\">"
         . $bot['achievementsNum']
         . ": </span>"
         . $bot['achievements']
         . "</td><td class=\"bot_division\" style=\"font-size: 85%\">"
         . $bot['division']
         . "</td><td>"
         . $bot['status']
         . "</td>"
         . "<td class=\"bot_updated\" style=\"font-size: 85%;\">"
         . $bot['update']
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
	$res = mysql_query("SELECT game_id,bot1,bot2,result,datetime,note FROM games WHERE result='1' OR result='2' OR result='draw' ORDER BY datetime DESC LIMIT 100;");
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

<h2>Random games:</h2>
<ul>
<?php
    $res = mysql_query("SELECT game_id,bot1,bot2,datetime FROM games WHERE (result='1' OR result='2' OR result='draw') AND (datetime >= (UNIX_TIMESTAMP() - 7 * 86400) ) ORDER BY RAND() LIMIT 10;");
    while ($line = mysql_fetch_assoc($res)) {
        $resBot1 = mysql_query("SELECT full_name,bot_race FROM fos_user WHERE id='".$line['bot1']."' LIMIT 1");
        $resBot2 = mysql_query("SELECT full_name,bot_race FROM fos_user WHERE id='".$line['bot2']."' LIMIT 1");
        $bot1 = mysql_fetch_assoc($resBot1);
        $bot2 = mysql_fetch_assoc($resBot2);
        $resElo1 = mysql_query("SELECT elo_rating FROM historical_elo_ratings WHERE bot_id='".$line['bot1']."' ORDER BY date DESC LIMIT 1");
        $resElo2 = mysql_query("SELECT elo_rating FROM historical_elo_ratings WHERE bot_id='".$line['bot2']."' ORDER BY date DESC LIMIT 1");
        $elo1 = mysql_fetch_assoc($resElo1);
        $elo2 = mysql_fetch_assoc($resElo2);
        $eloDiff = abs($elo1['elo_rating'] - $elo2['elo_rating']);

        echo '<li>';
        echo 'ELO diff '.$eloDiff.' <a href="./index.php?action=botDetails&bot='.urlencode($bot1['full_name']).'">'.$bot1['full_name'].'</a> ('.$bot1['bot_race'].') vs. <a href="./index.php?action=botDetails&bot='.urlencode($bot2['full_name']).'">'.$bot2['full_name'].'</a> ('.$bot2['bot_race'].') '.date("Y-m-d H:i:s",$line['datetime']);
        $repUrl = getReplayFileURL($line['game_id'], $bot1['full_name'], $bot2['full_name']);
        if ($repUrl != '') {
          echo ': <a href="'.$repUrl.'" target="_blank">.rep</a> / <a href="http://www.openbw.com/replay-viewer/?rep='.urlencode($repUrl).'" target="_blank">watch</a>';
        }
        echo '</li>';
    }
?>
</ul>

<?php
if (!empty($eloRatingsRemoteURL)) {
    ?>
    <hr>
    <div>*Note: <?php echo($eloRatingsNote); ?></div>
    <?php
}
?>

<?php
// save the cached version of this page
file_put_contents($cachePath, ob_get_contents());
