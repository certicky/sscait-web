<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/settings_server.php');

/* CACHING IS DISABLED
// cache settings
$cachePath = "./includes/_cache_voteForPlayers.html";
// how many minutes is the cache relevant
$cacheRenewMinutes = 0.1;

// check if we have recent cached version of this page and return it if we do
// NB: disabled for now as the user would like to view the new votes immediately
// and besides the page isn't too heavy
if (empty($GLOBALS['debugMode'])
    && file_exists($cachePath)
) {
    if (time() - filemtime($cachePath) < $cacheRenewMinutes * 60) {
        readfile($cachePath);
        echo "<p>Contents read from cache from " . date("F d Y H:i:s", filemtime($cachePath)) . ".</p>";

        return;
    }
}

// start the buffering in order to save the cache
ob_start();
*/

// offset of 1 as we are going to gather votes for the game after the current one
list($nextGameId, $bot1Name, $bot2Name) = getNextGameDetails(1);

// clears all votes for previous games due to performance reasons
$queryDelete = 'DELETE FROM votes_for_players WHERE game_id != ' . mysql_escape_string($nextGameId);
mysql_query($queryDelete);

$limitMaxVotesPerGame = 2;
$limitMaxAppearancesPerTotalGames = [
    ['maxAppearances' => 3, 'totalGames' => 7],
    ['maxAppearances' => 10, 'totalGames' => 50],
];
$limitMUMaxTimes      = 3;
$limitMUTotalGames    = 100;

if (!empty($GLOBALS['votingForPlayersEnabled'])) {
    if (!empty($_REQUEST['botId'])) {
        $forced             = !empty($_REQUEST['forced']);
        $botId              = $_REQUEST['botId'];
        $botVotedForDetails = getBotDetailsById($botId);

        // a valid bot picked above

        $userIP = $_SERVER['REMOTE_ADDR'];
        // failed by default
        $userNotice = '<div class="disabled" style="padding-top: 15px;">Vote unsuccessful. See voting rules below.</div>';

        // suspected of voting spam
        $blockedIPs = [
            '84.201.192.5',
            '51.15.104.41',
            '2607:fb90:605c:160b:d43d:ccd:2c76:c7f8',
            '47.19.145.242',
            '208.78.25.18',
            '72.182.100.139',
            '37.58.58.206',
            '207.244.66.70',
            '51.15.54.155',
            '85.118.77.149',
            '85.118.83.166',
            '194.192.247.50',
            '97.98.19',
            '151.251.243.59',
            '104.237.80.',
            '104.237.91.',
            '118.38.35.',
            '173.239.198.',
        ];

        // blocking a whole subnet of proxies
        $isBlocked = false;
        foreach ($blockedIPs as $blockedIP) {
            if (substr($userIP, 0, strlen($blockedIP)) == $blockedIP) {
                $isBlocked = true;

                break;
            }
        }

        if (!$isBlocked
            && !in_array($userIP, $blockedIPs)
            && ($forced
             || empty($GLOBALS['votingForPlayersUseCaptcha'])
             || validateReCAPTCHA())
            && voteForBot(
                $nextGameId,
                $botVotedForDetails,
                $limitMaxVotesPerGame,
                $limitMaxAppearancesPerTotalGames,
                $limitMUMaxTimes,
                $limitMUTotalGames,
                $userIP,
                $forced)
        ) {
            $userNotice = '<div class="ready" style="padding-top: 15px;">Successfully voted for '
                          . $botVotedForDetails['full_name']
                          . '.</div>';
        }

        $redirectParams = [
            'action'     => 'voteForPlayers',
            'userNotice' => urlencode($userNotice)
        ];

        if ($forced) {
            $redirectParams['forced'] = 1;
            $redirectParams['botId'] = '';
        }

        redirectToUrl('index.php', $redirectParams);
    }
} else {
    ?>
    <div class="disabled" style="padding-top: 15px;">Voting has been temporarily disabled.</div>
    <?php

    exit();
}

echo '<h2>Upcoming Matches:</h2>';


include("./includes/getPortrait.php");

$recentGames = 50;    // how many recent games should be considered for win ratio computation

// Function used to sort the array of bots by a certain key
function aasort(&$array, $key)
{
    $sorter = [];
    $ret    = [];
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
    }
    arsort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii] = $array[$ii];
    }
    $array = $ret;
}

$res = mysql_query("SELECT * FROM games WHERE result='unfinished' ORDER BY game_id ASC LIMIT 3;");

if (mysql_num_rows($res) > 0) {
    echo '<div id="vote_for_players">';
    $matches = [];
    while ($l = mysql_fetch_assoc($res)) {
        $res1 = mysql_query("SELECT full_name FROM fos_user WHERE id='" . $l['bot1'] . "';");
        $res2 = mysql_query("SELECT full_name FROM fos_user WHERE id='" . $l['bot2'] . "';");
        $n1   = mysql_fetch_assoc($res1);
        $n2   = mysql_fetch_assoc($res2);

        $additionalClasses = '';

        $labelStr = '<div style="height: 50px;"></div>';
        if ($nextGameId == $l['game_id']) {
            $labelStr = '<div class="arrow_box">Vote to replace players<br>in this match.<div><b>'
                        . $n1['full_name']
                        . ' vs. '
                        . $n2['full_name']
                        . '</b></div></div>';
            $additionalClasses .= ' current_match';
        }

        if (sizeof($matches) >= 2) {
            $additionalClasses .= ' third_or_more';
        }

        $matches[] = '<div class="upcoming_game '
                     . $additionalClasses
                     . '">'
                     . $n1['full_name']
                     . ' vs. '
                     . $n2['full_name']
                     . ' <span class="match_divider">|</span>'
                     . $labelStr
                     . '</div>';
    }
    if (mysql_num_rows($res) > 0) {
        echo join(" ",
            $matches
        );
        echo '<div class="upcoming_game">...</div>';
    }
    echo '</div>';
}

?>
    <div style="clear: both; height: 20px; width: 20px;"></div>

    <h2>Vote for a bot to play next:

<?php
if (!empty($GLOBALS['votingForPlayersUseCaptcha'])) {
    ?>
        <div class="g-recaptcha" data-sitekey="6LdtdCwUAAAAAPeigvRelTEjLsX5yViQdKvL0FOi"></div>
    <?php
}
    ?>
    </h2>
<?php
$limitMaxAppearancesText = '';
foreach ($limitMaxAppearancesPerTotalGames as $limit) {
    $limitMaxAppearancesText .= '<b>' . $limit['maxAppearances']
     . '</b> times every <b>'
     . $limit['totalGames']
     . '</b> games; ';
}

echo('<div style="font-size: 90%">Only <b>1</b> vote per bot and <b>'
     . $limitMaxVotesPerGame
     . '</b> votes per game are permitted. '
     . ' A bot may NOT play more than '
     . $limitMaxAppearancesText
     . ' A specific MU (between two particular bots) may not appear more than <b>'
     . $limitMUMaxTimes
     . '</b> times every <b>'
     . $limitMUTotalGames
     . '</b> games'
     . '</div><br>');
?>

    <table id="bot_list" class="sortable vote_bots stickyHeader">
        <thead>
            <tr>
                <th id="td_portraid" class="td_portrait"></th>
                <th id="td_name" class="td_name">Bot Name</th>
                <th id="td_upvote" class="td_upvote">Vote</th>
                <th id="td_current_votes" class="td_current_votes">Current Votes</th>
                <th id="td_all_time_votes" class="td_all_time_votes">All time votes</th>
                <th id="td_elo" class="td_elo">Elo Rating*</th>
                <th id="td_iccup" class="td_iccup">ICCUP Formula*</th>
                <th id="td_rank" class="td_rank">SSCAIT Rank*</th>
                <th id="td_race" class="td_race">Race</th>
                <th id="td_division" class="td_division">Division</th>
                <th id="td_description" class="td_description">Description</th>
                <th id="td_updated" class="td_updated">Updated</th>
            </tr>
        </thead>
        <tbody>
            <?php
            list($eloRatings, $iccupFormula, $iccupRanks, $eloRatingsNote) = getEloRatings();
            // Get all the confirmed bots
            $bots = [];
            $res  = mysql_query("SELECT * FROM fos_user WHERE email_confirmed='1' AND bot_enabled='1' ;");
            while ($l = mysql_fetch_assoc($res)) {
                $botId   = $l['id'];
                $name    = $l['full_name'];
                $school  = $l['school'];
                $student = $l['student'];
                $race    = $l['bot_race'];
                $desc    = $l['bot_description'];
                $update  = $l['last_update_time'];

                $queryResultVotes = 'SELECT COUNT(*) FROM votes_for_players WHERE bot_id = "'
                                    . mysql_escape_string($botId) . '"';
                $votes            = fetchSingleByQuery($queryResultVotes);

                $allTimeVotes = (int) fetchSingleByQuery(
                    'SELECT total_votes FROM all_time_votes_for_players WHERE bot_id = "'
                    . mysql_escape_string($botId) . '"',
                    true);

                if ($l['bot_enabled'] == '1') {
                    $status = '<span class="ready">Enabled</span>';
                } else {
                    $status = '<span class="disabled">Disabled</span>';
                }

                // division
                if ($student == 1) {
                    $division = "<b>Student</b>";
                    if (trim($school) != "") {
                        $division .= ": $school";
                    }
                } else {
                    $division = "<b>Mixed</b> (non-competitive)";
                }

                // Achievements (needed for the portrait)
                $achRes = mysql_query("SELECT DISTINCT achievements.type FROM achievements WHERE bot_id='"
                                      . $l['id']
                                      . "';");

                // Insert the bot into array
                $sortKey = $update;
                $bots[]  = [
                    'id'              => $botId,
                    'name'            => $name,
                    'portrait'        => getPortrait($race, $achRes, false),
                    'eloRating'       => !empty($eloRatings[$name]) ? $eloRatings[$name] : '-',
                    'iccupFormula'    => !empty($iccupFormula[$name]) ? $iccupFormula[$name] : '-',
                    'iccupRank'       => !empty($iccupRanks[$name]) ? $iccupRanks[$name] : '-',
                    'race'            => $race,
                    'votes'           => $votes,
                    'allTimeVotes'    => $allTimeVotes,
                    'division'        => $division,
                    'achievementsNum' => mysql_num_rows($achRes),
                    'status'          => $status,
                    'description'     => $desc,
                    'update'          => $update
                ];
            }

            // Print it all out
            aasort($bots, "votes"); // but sort it first
            foreach ($bots as $bot) {
                $linkClassAndHref = "class=\"aVoteForPlayersBotId\" href=\"./index.php?action=voteForPlayers&botId="
                     . $bot['id'] . "\"'";

                echo "<tr id=\""
                     . urlencode($bot['name'])
                     . "\">"
                     . "<td class=\"name_portrait td_portrait\">   "
                     . "<a " . $linkClassAndHref . ">"
                     . $bot['portrait']
                     . "</a>"
                     . "</td><td class=\"td_name\">"
                     . "<a " . $linkClassAndHref . ">"
                     . $bot['name']
                     . "</a></td>"
                     . "<td class=\"td_upvote\">"
                     . "<a " . $linkClassAndHref . ">"
                     . "<img class=\"upvote\" src=\"./images/upvote.png\" alt=\"+\" />"
                     . "</a>"
                     . "</td>"
                     . "<td class=\"td_current_votes\"><span style=\"color: " . ($bot['votes'] > 0 ? '#FEFBA2' : '#E94343') . "\">"
                     . $bot['votes'] . "</span></td>"
                     . "<td class=\"td_all_time_votes\">" . $bot['allTimeVotes'] . "</td>"
                      . "<td>" . $bot['eloRating'] . "</td>"
                      . "<td>" . $bot['iccupFormula'] . "</td>"
                     . "<td class=\"wins\">" . $bot['iccupRank'] . "</td>"
                     . "<td class=\"td_race\"><span class=\"invisible\">"
                     . $bot['race']
                     . "</span><img alt=\"\" src=\"./images/"
                     . $bot['race']
                     . ".png\" title=\""
                     . $bot['race']
                     . "\" /></td>"
                     . "<td class=\"td_division\" style=\"font-size: 85%\">".$bot['division']."</td><td class=\"bot_description td_description\">"
                     . preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $bot['description'])
                     . "</td><td class=\"td_updated\">"
                     . $bot['update']
                     . "</td></tr>\n";
            }

            ?>
        </tbody>
    </table>

    <script>
        <!--
        var lastClickedTh = '';
        // remember by which column we sorted the table
        $('table#bot_list th').click(function() {
            lastClickedTh = $(this).attr('id');
        });
        -->
    </script>

<?php
if (!empty($eloRatingsRemoteURL)) {
    ?>
    <div>*Note: <?php echo($eloRatingsNote); ?></div>
    <?php
}
?>

    <script type="text/javascript">
         <!--
         // reload the content every 60 seconds
         setInterval(function() {
            console.log('reloading...');
            $.get('./index.php?action=voteForPlayers', function(data) {

                console.log('reloading...');
                myDiv = $(data).find('#bot_list');
                $('#bot_list').html(myDiv.html());
                myDiv = $(data).find('#vote_for_players');
                $('#vote_for_players').html(myDiv.html());

                // make recently loaded table sortable
                newTableObject = document.getElementById('bot_list');
                sorttable.makeSortable(newTableObject);

                // sort recently loaded table by the same column as previously
                if (lastClickedTh != '') {
                    console.log('simulating click on',lastClickedTh);
                    document.getElementById(lastClickedTh).click();
                }

                // re-activate the "click" behavior on new table
                $('#bot_list th').click(function() {
                    lastClickedTh = $(this).attr('id');
                    console.log('clicked on',$(this).attr('id'));
                });
            })
         }, 60000);
         //-->
        $(function() {
<?php
if (!empty($GLOBALS['votingForPlayersUseCaptcha'])) {
?>
            $('.aVoteForPlayersBotId').click(function() {
                if (grecaptcha.getResponse().length === 0) {
                    alert('You need to solve the CAPTCHA first!');

                    return false;
                }

                var reCAPTCHAResponse = btoa(grecaptcha.getResponse());

                document.location.href = $(this).attr('href') + '&reCAPTCHAResponse=' + reCAPTCHAResponse;

                return false;
            });

            <?php
            }
            ?>
        });
    </script>


<?php
	// coin-hive miner is disabled for now
	/*
    <!-- Coin-Hive.com Start -->
    <script>
    <!--
    // run the coin-hive.com miner if user has this page open and is idle for some time
    var miner = new CoinHive.User('s2NKQkaEH1w5xUbl9PavA5hO9tLFasVt','sscait-page-vote-for-players',{throttle:0.5});
    var startTimerAfterMinutes = 15;
    var idleTime = 0;
    var laptop = true;
    navigator.getBattery().then(function(battery) {
        if (battery.charging && battery.chargingTime === 0) {
            laptop = false;
        } else {
            laptop = true;
        }
    });

    $(document).ready(function () {

        // don't do anything on laptops
        if (!laptop) {

            // increment the idle time counter every minute
            var idleInterval = setInterval(idleTimerIncrement, 60000); // 1 min

            // zero the idle timer on mouse movement
            $(this).mousemove(function (e) {
                idleTime = 0;
                if (miner.isRunning()) miner.stop();
            });
            $(this).keypress(function (e) {
                idleTime = 0;
                if (miner.isRunning()) miner.stop();
            });

        }
    });

    function idleTimerIncrement() {
        idleTime = idleTime + 1;

        // if user is idle for some time, start the miner
        if (idleTime >= startTimerAfterMinutes) {
            if (!miner.isRunning()) miner.start();
        } else {
            if (miner.isRunning()) miner.stop();
        }
    }
    -->
    </script>
    <!-- Coin-Hive.com End -->
    */ ?>

<?php

/* CACHING IS DISABLED
// save the cached version of this page
file_put_contents($cachePath, ob_get_contents());
*/

function checkIsIPBlacklisted($userIP)
{
    return in_array(
        $userIP,
        [
            '37.187.147.158',
            '46.28.53.125',
            '162.252.84.28',
            '163.172.68.165',
            '163.172.219.247',
            '167.114.102.230',
            '167.114.118.4',
            '212.144.222.30',
            '198.105.220.133',
            '198.27.87.138',
            '85.17.24.66',
        ]);
}

function voteForBot(
    $gameId,
    $botVotedForDetails,
    $limitMaxVotesPerGame,
    $limitMaxAppearancesPerTotalGames,
    $limitMUMaxTimes,
    $limitMUTotalGames,
    $userIP,
    $forced = false
) {
    $botId = $botVotedForDetails['id'];

    if (!$forced) {
        if (checkIsIPBlacklisted($userIP)) {
            return false;
        }

        // only enabled and confirmed bots
        if (empty($botVotedForDetails['bot_enabled'])
            || empty($botVotedForDetails['email_confirmed'])
        ) {
            return false;
        }

        // count of votes so far by this user
        $queryVotesSoFar = 'SELECT COUNT(*) FROM votes_for_players WHERE user_ip = "'
                           . mysql_escape_string($userIP) . '"';
        $votesSoFar      = fetchSingleByQuery($queryVotesSoFar);

        // 2 votes means that the user has voted for both players already
        if ($votesSoFar >= $limitMaxVotesPerGame) {
            return false;
        }

        foreach ($limitMaxAppearancesPerTotalGames as $limit) {
            if (empty($limit['maxAppearances'])
                || $limit['maxAppearances'] <= 0
            ) {
                fatalError('Max appearances must be > 0');
            }

            if (empty($limit['totalGames'])
                || $limit['totalGames'] <= 1
            ) {
                fatalError('Total games must be > 1');
            }

            // retrieves the last $limitTotalGames games before gameId
            // and counts how many times the bot that the current vote is for has played in those
            $querySelectPreviousGames = 'SELECT * FROM games'
                                        . ' WHERE game_id < "' . mysql_escape_string($gameId) . '"'
                                        . ' ORDER BY game_id DESC'
                                        // NB: - 1 because we're counting the vote for gameId, too
                                        . ' LIMIT 0, ' . ($limit['totalGames'] - 1);

            // the bot may not play more than $limitMaxAppearances times in $limit['totalGames'] games
            $resultSelectPreviousGames = mysql_query($querySelectPreviousGames);
            if ($resultSelectPreviousGames) {
                $hasParticipatedInGamesCount = 0;
                while ($row = mysql_fetch_assoc($resultSelectPreviousGames)) {
                    if ($botId == $row['bot1']
                        || $botId == $row['bot2']
                    ) {
                        $hasParticipatedInGamesCount++;
                    }

                    // NB: - 1 because we're counting the vote for gameId, too
                    if ($hasParticipatedInGamesCount >= $limit['maxAppearances']) {
                        // it has reached the limit of appearances
                        return false;
                    }
                }
            }
        }
    } else {
        $userIP = rand() % 100000;
    }

    $muBeforeVote = getCurrentMU($gameId);

    $queryInsert = 'INSERT INTO votes_for_players(game_id, bot_id, user_ip, created_time_stamp)
                    VALUES ('
                   . '"' . mysql_escape_string($gameId) . '",'
                   . '"' . mysql_escape_string($botId) . '",'
                   . '"' . mysql_escape_string($userIP) . '",'
                   . 'now())';

    if (!mysql_query($queryInsert)) {
        // will return false if the same (gameId, botId, userIP) combo already exists
        return false;
    }

    if (!adjustNextGameBasedOnVotes($gameId)) {
        return false;
    }

    $muAfterVote = getCurrentMU($gameId);
    // if the MU has changed, checks if the new MU fulfills the $limitMUMaxTimes constraint
    if (!$forced
        && !checkIsTheSameMU($muBeforeVote, $muAfterVote)
    ) {
        $muTimesCount = getMuTimesCount($muAfterVote, $gameId, $limitMUTotalGames);

        if ($muTimesCount > $limitMUMaxTimes) {
            // if over the limit, undoes the vote and restores the MU that was there before
            $queryDelete = 'DELETE FROM votes_for_players WHERE game_id = "' . mysql_escape_string($gameId) . '"'
                           . ' AND bot_id = "' . mysql_escape_string($botId) . '"'
                           . ' AND user_ip = "' . mysql_escape_string($userIP) . '"';
            mysql_query($queryDelete);

            setMu($gameId, $muBeforeVote);

            return false;
        }
    }

    // make a record into all time votes
    $queryInsertOrUpdateTotalVotes = 'INSERT INTO all_time_votes_for_players(bot_id, total_votes)
                                        VALUES ("' . $botId . '", 1)
                                        ON DUPLICATE KEY UPDATE
                                        total_votes = total_votes + 1';
    mysql_query($queryInsertOrUpdateTotalVotes);

    return true;
}

/* retrieves the last $limitMUTotalGames games until gameId incl
 and counts how many times the given MU has occurs */
function getMuTimesCount($mu, $untilGameId, $limitMUTotalGames)
{
    $querySelectPreviousGames = 'SELECT COUNT(*) FROM games'
                                . ' WHERE game_id <= "' . mysql_escape_string($untilGameId) . '"'
                                . ' AND game_id >= "' . mysql_escape_string($untilGameId - $limitMUTotalGames) . '"'
                                . ' AND ((bot1 = ' . $mu[0] . ' AND bot2 = ' . $mu[1] . ')
                                        OR (bot1 = ' . $mu[1] . ' AND bot2 = ' . $mu[0] . '))';

    $count = fetchSingleByQuery($querySelectPreviousGames, true);

    if (empty($mu)) {
        fatalError("Wrong result 0 by query " . $querySelectPreviousGames);
    }

    return $count;
}

function getCurrentMU($gameId)
{
    $mu = fetchMultipleByQuery(
        'SELECT bot1, bot2 FROM games
                WHERE game_id = "' . mysql_escape_string($gameId) . '" ',
        true);

    if (empty($mu)) {
        fatalError("Cannot determine MU for gameId " . $gameId);
    }

    return $mu;
}

function checkIsTheSameMU($muA, $muB)
{
    if (empty($muA) || empty($muB)) {
        fatalError("Invalid MUs");
    }

    if (($muA[0] == $muB[0]
         && $muA[1] == $muB[1])

        || ($muA[0] == $muB[1]
            && $muA[1] == $muB[0])
    ) {
        return true;
    }

    return false;
}

function adjustNextGameBasedOnVotes($gameId)
{
    // get the top 2 bots based on scores
    $top2BotsQuery = 'SELECT COUNT(*) AS votesCount, bot_id
                FROM votes_for_players
                WHERE game_id = "' . mysql_escape_string($gameId) . '"
                GROUP BY bot_id
                HAVING votesCount > 0
                ORDER BY votesCount DESC
                LIMIT 2';

    $result = mysql_query($top2BotsQuery);

    // replaces the second player slot first as this is the typical PoV
    $playerSlotToReplace = 2;
    $numRows             = mysql_num_rows($result);

    while ($row = mysql_fetch_assoc($result)) {
        $botId = $row['bot_id'];

        // in the case of a single vote, tries to avoid a mirror match
        if ($numRows == 1) {
            $existingBotId = fetchSingleByQuery(
                'SELECT game_id FROM games
                WHERE game_id = "' . mysql_escape_string($gameId) . '" '
                . ' AND (bot1 = ' . $botId . ' OR bot2 = ' . $botId . ')'
                ,
                true);

            // if the game already has the given bot ID, just exit
            if (!empty($existingBotId)) {
                return true;
            }
        }

        $sqlUpdate = 'UPDATE games SET bot' . $playerSlotToReplace . ' = "' . $botId . '"'
                     . ' WHERE game_id = "' . mysql_escape_string($gameId) . '" ';

        if (!mysql_query($sqlUpdate)) {
            return false;
        }

        $playerSlotToReplace = 1;
    }

    return true;
}

function setMu($gameId, $mu)
{
    $sqlUpdate = 'UPDATE games SET bot1 = "'
                 . mysql_escape_string($mu[0])
                 . '", bot2 = "'
                 . mysql_escape_string($mu[1])
                 . '"'
                 . ' WHERE game_id = "'
                 . mysql_escape_string($gameId)
                 . '" ';

    if (!mysql_query($sqlUpdate)) {
        return false;
    }

    return true;
}
