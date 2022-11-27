<?php

// SETTINGS
$winRateChartYears = 3;				// display last N years in the win rate chart
$winRateChartMinGamesPerMonth = 30;	// don't display those months, where bot less than N games
$opponentChartMinGames = 50;		// don't display the opponents with less than N games against selected bot

// check the $_GET['bot'] param and load bot's ID, name, dascription ...
$botId = null;
$botName = null;
$botDescription = null;
$botType = null;
$botLastUpdate = null;
if (isset($_GET['bot']) && (trim($_GET['bot']) != '')) {
	$b = trim(mysql_real_escape_string($_GET['bot']));
	$q = "SELECT * FROM fos_user WHERE full_name = '".$b."' AND email_confirmed = 1 LIMIT 1";
	$res = mysql_query($q);
	while ($line = mysql_fetch_assoc($res)) {
		$botId = $line['id'];
		$botName = $line['full_name'];
		$botDescription = $line['bot_description'];
		$botType = $line['bot_type'];
		$botLastUpdate = $line['last_update_time'];
	}
}

if ($botId == null) {
	echo '<p>Bot not found!</p>';

// if bot is found ...
} else {

	// get the ELO ratings and ICCUP rank
	list($eloRatings, $iccupFormula, $iccupRanks, $eloRatingsNote) = getEloRatings();
	arsort($eloRatings);
	$botELO = $eloRatings[$botName];
	$botIccupFormula = $iccupFormula[$botName];
	$botICCUP = $iccupRanks[$botName];

	// get the wins / losses / draws
	$winRes =  mysql_query("SELECT count(game_id) FROM `games` WHERE ((bot1='".$botId."' AND result='1') or (bot2='".$botId."' AND result='2'))");
	$lossRes = mysql_query("SELECT count(game_id) FROM `games` WHERE ((bot1='".$botId."' AND result='2') or (bot2='".$botId."' AND result='1'))");
	$drawsRes = mysql_query("SELECT count(game_id) FROM `games` WHERE  result='draw' AND ((bot1='".$botId."') OR (bot2='".$botId."'))");
	$wins = mysql_fetch_row($winRes); $wins = $wins[0];
	$losses = mysql_fetch_row($lossRes); $losses = $losses[0];
	$draws = mysql_fetch_row($drawsRes); $draws = $draws[0];

	// achievements
	$achi = "";
	$achiIndex = 0;
	$achRes = mysql_query("SELECT achievements.type,title,text FROM achievements,achievement_texts WHERE bot_id='".$botId."' AND achievements.type=achievement_texts.type ORDER BY datetime DESC;");
	while ($a = mysql_fetch_assoc($achRes)) {
		$achiIndex += 1;
		$classStr = "achievement_icon achievement_icon_small";
		$achi .= '<a title="'.$a['title'].' ('.lcfirst(trim($a['text'],".")).')" href="./index.php?action=achievements#'.$a['type'].'"><img class="'.$classStr.'" src="./images/achievements/small/'.$a['type'].'.png" alt="'.$a['title'].'. " /></a>';
	}

	?>
		<!-- Bot Name (title) -->
		<h2 style="margin-bottom: 0; padding-left: 10px !important;"><?php echo $botName; ?></h2>
		<div style="color: rgb(40,40,40); font-size: 95%; padding-left: 10px !important;">StarCraft AI Bot Profile</div>

		<!-- Basic Bot Info -->
		<table style="margin-top: 20px;">
			<tr><td style="vertical-align: top;">SSCAIT Description:</td><td style="<?php if (strlen($botDescription) > 50) echo 'font-size: 80%; color: rgb(40,40,40);'; ?>"><?php echo getTextWithClickableLinks($botDescription,$nofollow=true); ?></td></tr>
			<tr><td>Bot type:</td><td><?php echo $botType; ?></td></tr>
			<tr><td>ELO rating:</td><td><b><?php echo $botELO; ?></b></td></tr>
			<tr><td>ICCUP formula:</td><td><b><?php echo $botIccupFormula; ?></b></td></tr>
			<tr><td>SSCAIT rank:</td><td><b><?php echo $botICCUP; ?></b></td></tr>
			<?php 
			if ($GLOBALS["eliminationBracketPhase"]) {
			    ?>
    			  <tr>
    			  	<td colspan="2">
    			  		<div style="font-weight: bold; max-width: 600px; padding: 20px 0 10px 0; font-size: 80%;">We're currently resolving the elimination bracket, so most of the information is inaccessible to prevent spoiling the results. The data will be back soon!</div>
    			  	</td>
    			  </tr>
    			</table>
    			<?php    
			    die();
			}
			if ($GLOBALS["competitivePhase"]) {
			  ?>
			  <tr>
			  	<td colspan="2">
			  		<div style="font-weight: bold; max-width: 600px; padding: 20px 0 10px 0; font-size: 80%;">Note: While the competitive phase of SSCAIT is in progress, we only display the results from that specific phase here:</div>
			  	</td>
			  </tr>
			  <?php    
			}
			?>
			<tr><td>Wins:</td><td><?php echo $wins; ?></td></tr>
			<tr><td>Losses:</td><td><?php echo $losses; ?></td></tr>
			<tr><td>Draws:</td><td><?php echo $draws; ?></td></tr>
			<tr><td>Total Win Rate:</td><td><b><?php echo (intval($wins) / (intval($wins)+intval($losses)+intval($draws))); ?></b></td></tr>
      <tr><td>Achievements:</td><td><?php echo $achi; ?></td></tr>
		</table>

    <div style="margin: 20px 0; padding: 20px 10px 0 10px;  border-top: solid 1px gray;" id="liquipedia_desc">
      <span style="color: rgb(40,40,40);">Loading info from Liquipedia...</span>
    </div>
    <script>
      $.get('./liquipedia_description.php?bot_name=<?php echo urlencode($botName); ?>',function(data){$('#liquipedia_desc').html(data);});
    </script>

		<?php 
		// we don't display the following charts during competitive phase, because they require
		// the 'games' DB table
		if (!$GLOBALS["competitivePhase"]) {  
		?>
    	    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
    		<!--  Chart: Win Rate over time-->
    	    <script type="text/javascript">
    	      google.charts.load('current', {'packages':['corechart']});
    	      google.charts.setOnLoadCallback(drawChart);
    
    	      function drawChart() {
    	        var data = google.visualization.arrayToDataTable([
    				<?php
    
    				// prepare the list of names of allowed bots (few with best ELO + our bot of interest)
    				$allowedBots = array_keys(array_slice($eloRatings,0,4));
    				if (!in_array($botName,$allowedBots)) array_push($allowedBots,$botName);
    				array_walk($allowedBots, function(&$item) {
    					$item = "'".$item."'";
    				});
    
    				$bots = array();
    				$months = array();
    				$results = array();
    
    				// wins
    				$q = "
    				SELECT
    				DATE_FORMAT(FROM_UNIXTIME(datetime), '%Y%m') AS month,
    				full_name,
    				COUNT(game_id) AS game_count
    				FROM games JOIN (SELECT full_name, id FROM fos_user WHERE full_name IN (".implode(',',$allowedBots).")) AS fos_user ON (fos_user.id = games.bot1 OR fos_user.id = games.bot2)
    				WHERE
    				datetime > UNIX_TIMESTAMP(NOW() - INTERVAL $winRateChartYears YEAR)
    				AND ((result = 1 AND bot1=fos_user.id) OR (result = 2 AND bot2=fos_user.id))
    				GROUP BY
    				DATE_FORMAT(FROM_UNIXTIME(datetime), '%Y%m'),
    				full_name
    				ORDER BY DATE_FORMAT(FROM_UNIXTIME(datetime), '%Y%m') ASC
    				";
    
    				$res = mysql_query($q);
    				while ($line = mysql_fetch_assoc($res)) {
    				if (!in_array($line['month'],$months)) $months[] = $line['month'];
    				if (!in_array($line['full_name'],$bots)) $bots[] = $line['full_name'];
    				if (!array_key_exists($line['month'],$results)) $results[$line['month']] = array();
    				if (!array_key_exists($line['full_name'],$results[$line['month']])) $results[$line['month']][$line['full_name']] = array("wins" => 0,"losses" => 0);
    				$results[$line['month']][$line['full_name']]["wins"] = intval($line['game_count']);
    				}
    
    				// losses
    				$q = "
    				SELECT
    				DATE_FORMAT(FROM_UNIXTIME(datetime), '%Y%m') AS month,
    						full_name,
    						COUNT(game_id) AS game_count
    						FROM games JOIN (SELECT full_name, id FROM fos_user WHERE full_name IN (".implode(',',$allowedBots).")) AS fos_user ON (fos_user.id = games.bot1 OR fos_user.id = games.bot2)
    								WHERE
    								datetime > UNIX_TIMESTAMP(NOW() - INTERVAL $winRateChartYears YEAR)
    								AND ((result = 2 AND bot1=fos_user.id) OR (result = 1 AND bot2=fos_user.id))
    										GROUP BY
    										DATE_FORMAT(FROM_UNIXTIME(datetime), '%Y%m'),
    										full_name
    										ORDER BY DATE_FORMAT(FROM_UNIXTIME(datetime), '%Y%m') ASC
    										";
    
    										$res = mysql_query($q);
    										while ($line = mysql_fetch_assoc($res)) {
    										if (!in_array($line['month'],$months)) $months[] = $line['month'];
    										if (!in_array($line['full_name'],$bots)) $bots[] = $line['full_name'];
    										if (!array_key_exists($line['month'],$results)) $results[$line['month']] = array();
    										if (!array_key_exists($line['full_name'],$results[$line['month']])) $results[$line['month']][$line['full_name']] = array("wins" => 0,"losses" => 0);
    						$results[$line['month']][$line['full_name']]["losses"] = intval($line['game_count']);
    					}
    
    					// chart: 1st row
    					echo "['Month', ";
    					$pieces = array();
    					foreach ($bots as $bot) {
    						$pieces[] = "{label: '".$bot."', type: 'number'}";
    					}
    					echo implode(", ",$pieces)."], \n";
    
    					// chart: remaining rows
    					foreach ($months as $month) {
    						echo "['".substr($month,4,2)."-".substr($month,2,2)."', ";
    						$pieces = array();
    						foreach ($bots as $bot) {
    							if (isset($results[$month][$bot]['wins']) || isset($results[$month][$bot]['losses'])) {
    								// compute win rate
    								$totalWinsLosses = 0;
    								if (isset($results[$month][$bot]['wins'])) {
    									$totalWinsLosses += $results[$month][$bot]['wins'];
    									if (isset($results[$month][$bot]['losses'])) {
    										$totalWinsLosses += $results[$month][$bot]['losses'];
    										$winRate = $results[$month][$bot]['wins'] / ($results[$month][$bot]['wins']+$results[$month][$bot]['losses']);
    									} else {
    										$winRate = 1;
    									}
    								} else {
    									if (isset($results[$month][$bot]['losses'])) $totalWinsLosses += $results[$month][$bot]['losses'];
    									$winRate = 0;
    								}
    
    								// ignore those months, where bot didn't play enough games
    								if ($totalWinsLosses < $winRateChartMinGamesPerMonth) $winRate = "null";
    
    								$pieces[] = $winRate;
    
    							} else {
    								$pieces[] = "null";
    							}
    						}
    						echo implode(", ",$pieces)."], \n";
    					}
    
    				?>
    	        ]);
    
    	        var options = {
    	          title: 'Win Rate over <?php echo $winRateChartYears; ?> years',
    	          curveType: 'function',
    	          lineWidth: 2,
    	          backgroundColor: 'rgb(240,240,240)',
    	          legend: { position: 'bottom' },
    	        };
    
    	        var chart = new google.visualization.LineChart(document.getElementById('chart_win_rate_over_time'));
    
    	        chart.draw(data, options);
    	      }
    	    </script>
    		<div style="margin: 20px 0; padding-bottom: 20px; border-top: solid 1px gray; border-bottom: solid 1px gray;">
    			<div id="chart_win_rate_over_time" style="width: auto; height: 300px; "></div>
    			<div style="width: 75%; margin: 0 auto; text-align: center; color: rgb(40,40,40); font-size: 80%; padding-top: 10px; padding-bottom: 10px;">
    				Monthy win rate of <?php echo $botName;?> over last <?php echo $winRateChartYears; ?> years compared to <?php echo (sizeof($bots)-1);?> bots with best ELO.<br>
    				Months when bots played less than <?php echo $winRateChartMinGamesPerMonth; ?> games are not displayed.
    			</div>
    		</div>
    
    
    		<!--  Chart: Maps and Win Rate -->
    		<script>
    			google.charts.load('current', {packages: ['corechart', 'bar']});
    			google.charts.setOnLoadCallback(drawColColors);
    
    			function drawColColors() {
    			  var data = google.visualization.arrayToDataTable([
                                  ['Map', 'Win Rate', { role: 'style' }],
                                  <?php
                                  $maps = array();
                                  $qWins = "SELECT map, count(1) AS wins FROM `games` WHERE ((bot1=$botId AND result=1) OR (bot2=$botId AND result=2)) AND map != '' AND map != 'maps/sscai/(8)BGH.scm' GROUP BY map";
                                  $qLosses = "SELECT map, count(1) AS losses FROM `games` WHERE ((bot2=$botId AND result=1) OR (bot1=$botId AND result=2)) AND map != '' AND map != 'maps/sscai/(8)BGH.scm' GROUP BY map";
                                  $resWins = mysql_query($qWins);
                                  while ($line = mysql_fetch_assoc($resWins)) {
                                  	if (!isset($maps[$line['map']])) $maps[$line['map']] = array('wins'=>0.0,'losses'=>0.0);
                                  	$maps[$line['map']]['wins'] = intval($line['wins']);
                                  }
                                  $resLosses = mysql_query($qLosses);
                                  while ($line = mysql_fetch_assoc($resLosses)) {
                                  	if (!isset($maps[$line['map']])) $maps[$line['map']] = array('wins'=>0.0,'losses'=>0.0);
                                  	$maps[$line['map']]['losses'] = intval($line['losses']);
                                  }
                                  foreach ($maps as $k => $v) {
                                  	if ($maps[$k]['losses'] == 0) {
                                  		$mapWR = 1;
                                  	} else {
                                  		$mapWR = ($maps[$k]['wins'] / ($maps[$k]['wins']+$maps[$k]['losses']));
                                  	}
                                  	echo "['".str_ireplace('maps/sscai/','',str_ireplace('.scm','',str_ireplace('.scx','',$k)))."', $mapWR, 'color: #".getColorCodeFromString($k)."' ],\n";
                                  }
                                  ?>
                             ]);
    		      var options = {
    		        title: 'Win Rate by Map',
    		        backgroundColor: 'rgb(240,240,240)',
    		        hAxis: {
    		        	textStyle : {
    		          		fontSize: 9,
    		        	}
    		        },
    		        legend: {position: 'none'}
    		      };
    
    		      var chart = new google.visualization.ColumnChart(document.getElementById('chart_maps'));
    		      chart.draw(data, options);
    		    }
    		</script>
    		<div style="margin: 20px 0; padding-bottom: 20px; border-bottom: solid 1px gray;">
    			<div id="chart_maps" style="width: auto; height: 300px; "></div>
    		</div>
    
    		<!--  Chart: Opponents and Win Rate -->
    		<script>
    			google.charts.load('current', {packages: ['corechart', 'bar']});
    			google.charts.setOnLoadCallback(drawColColors);
    
    			function drawColColors() {
    			  var data = google.visualization.arrayToDataTable([
                                  ['Opponent', 'Win Rate', { role: 'style' }],
                                  <?php
                                  $opponents = array();
                                  $qWins = "SELECT (CASE WHEN bot2 = $botId THEN (SELECT full_name FROM fos_user WHERE id=bot1) ELSE (SELECT full_name FROM fos_user WHERE id=bot2) END) AS opponent, count(1) AS wins FROM `games` WHERE ((bot1=$botId AND result=1) OR (bot2=$botId AND result=2)) GROUP BY opponent";
                                  $qLosses = "SELECT (CASE WHEN bot2 = $botId THEN (SELECT full_name FROM fos_user WHERE id=bot1) ELSE (SELECT full_name FROM fos_user WHERE id=bot2) END) AS opponent, count(1) AS losses FROM `games` WHERE ((bot2=$botId AND result=1) OR (bot1=$botId AND result=2)) GROUP BY opponent";
                                  $resWins = mysql_query($qWins);
                                  while ($line = mysql_fetch_assoc($resWins)) {
                                  	if (!isset($opponents[$line['opponent']])) $opponents[$line['opponent']] = array('wins'=>0.0,'losses'=>0.0);
                                  	$opponents[$line['opponent']]['wins'] = intval($line['wins']);
                                  }
                                  $resLosses = mysql_query($qLosses);
                                  while ($line = mysql_fetch_assoc($resLosses)) {
                                  	if (!isset($opponents[$line['opponent']])) $opponents[$line['opponent']] = array('wins'=>0.0,'losses'=>0.0);
                                  	$opponents[$line['opponent']]['losses'] = intval($line['losses']);
                                  }
                                  foreach ($opponents as $k => $v) {
                                  	// skip opponents with too few games against us
                                  	if (($opponents[$k]['wins']+$opponents[$k]['losses']) < $opponentChartMinGames) continue;
                                  	if ($k == '') continue;
    
                                  	if ($opponents[$k]['losses'] == 0) {
                                  		$opponentWR = 1;
                                  	} else {
                                  		$opponentWR = ($opponents[$k]['wins'] / ($opponents[$k]['wins']+$opponents[$k]['losses']));
                                  	}
                                  	echo "['".str_ireplace('maps/sscai/','',str_ireplace('.scm','',str_ireplace('.scx','',$k)))."', $opponentWR, 'color: #".getColorCodeFromString($k)."' ],\n";
                                  }
                                  ?>
                             ]);
    		      var options = {
    		        title: 'Win Rate by Opponent',
    		        backgroundColor: 'rgb(240,240,240)',
    		        hAxis: {
    		        	textStyle : {
    		          		fontSize: 9,
    		        	},
    		        	slantedTextAngle : 90
    		        },
    		        legend: {position: 'none'}
    		      };
    
    		      var chart = new google.visualization.ColumnChart(document.getElementById('chart_opponents'));
    		      chart.draw(data, options);
    		    }
    		</script>
    		<div style="margin: 20px 0; padding-bottom: 20px; border-bottom: solid 1px gray;">
    			<div id="chart_opponents" style="width: auto; height: 300px; "></div>
    			<div style="width: 75%; margin: 0 auto; text-align: center; color: rgb(40,40,40); font-size: 80%; padding-top: 10px; padding-bottom: 10px;">
    				Win rate of <?php echo $botName;?> against all the opponents with at least <?php echo $opponentChartMinGames; ?> mutual games.
    			</div>
    		</div>
    		
		<?php
		} // end of graphs that are NOT displayed during competitive phase
		?>

		<!-- Downloads -->
		<table style="margin-bottom: 20px;">
			<tr><td>Last updated:</td><td><?php echo $botLastUpdate; ?></td></tr>
			<tr><td>Download bot binary:</td><td><?php echo "<a target=\"_blank\" href=\"./bot_binary.php?bot=".urlencode($botName)."\">binary</a>"; ?></td></tr>
			<tr><td>Download bwapi.dll:</td><td><?php echo "<a target=\"_blank\" href=\"./bot_binary.php?bot=".urlencode($botName)."&amp;bwapi_dll=true\">bwapi.dll</a>"; ?></td></tr>
		</table>
		
		<?php 
		
		if (is_dir($GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"]."/".strtoupper($botName))) {
			echo '<div style="padding-left: 10px;">Latest replays:</div>';
			echo '<table style="margin: 5px 0 20px 0;">';
			
			// Gets each entry
			$ents = array();
			chdir($GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"]."/".strtoupper($botName));
			array_multisort(array_map('filemtime', ($files = glob("*.{REP,rep}", GLOB_BRACE))), SORT_DESC, $files);
			foreach($files as $filename) {
				array_push($ents,$filename);
			}
			$i = 0;
			foreach($ents as $e) {
				$i += 1;
				if ($i > 30) continue;
				if (substr($e,0,1) == '.') continue;
				if (is_dir("./".strtoupper($botName)."/".$e)) continue;
				if (stripos($e,".rep") == 0) continue;
				$mtime=date("Y-n-j H:i", filemtime($GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"]."/".strtoupper($botName)."/".$e));
				echo '<tr><td><a href="'."./Replays/".strtoupper($botName)."/".$e.'">'.$e.'</a></td><td>'.$mtime.', '.ceil(filesize($GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"]."/".strtoupper($botName)."/".$e)/1024).' kB</td><td>[ <a href="http://www.openbw.com/replay-viewer/?rep=https%3A%2F%2Fsscaitournament.com%2FReplays%2F'.urlencode(strtoupper($botName)."/".$e).'" target="_blank">watch in OpenBW</a> ]</td></tr>';
			}
			echo '</table>';
		}
		
		?>

<?php } ?>

