<?php

// Get all the confirmed bots
list($eloRatings, $iccupFormula, $iccupRanks, $eloRatingsNote) = getEloRatings();
$bots = array();
$res = mysql_query("SELECT * FROM fos_user WHERE email_confirmed='1';");
while ($l = mysql_fetch_assoc($res)) {
	$name = $l['full_name'];

	// Insert the bot into array
    $bots[$l['id']] = [
        'id'              => $l['id'],
        'name'            => $name,
        'eloRating'       => !empty($eloRatings[$name]) ? $eloRatings[$name] : '-',
    ];
}

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

    $name = $bots[$id]['name'];

    if (!isset($historicalEloRatings[$date])) {
        $historicalEloRatings[$date] = [];
    }

    $historicalEloRatings[$date][$id] = $eloRating;
}

$chartLabelsString = '';
$chartDatasetsString = '';
foreach ($historicalEloRatings as $date => $eloRatingsForDate) {
    $chartLabelsString .= '"' . $date . '",';
}

$botsEloRatings = [];
foreach($bots as $id => &$bot) {
    $chartsData = [];
    foreach ($historicalEloRatings as $date => $eloRatingsForDate) {
        $chartsData[] = empty($eloRatingsForDate[$bot['id']]) ? START_ELO : $eloRatingsForDate[$bot['id']];
    }

    $latestEloRating = empty($chartsData) ? START_ELO : $chartsData[count($chartsData) - 1];

    // only renders bots with ELO rating >= START_ELO
    if ($latestEloRating <= START_ELO) {
        continue;
    }

    $botsEloRatings[$id] = $latestEloRating;
}

arsort($botsEloRatings);

foreach($botsEloRatings as $id => $eloRating) {
    $chartsData = [];
    foreach ($historicalEloRatings as $date => $eloRatingsForDate) {
        $chartsData[] = empty($eloRatingsForDate[$id]) ? START_ELO : $eloRatingsForDate[$id];
    }

    $latestEloRating = empty($chartsData) ? START_ELO : $chartsData[count($chartsData) - 1];

    $name = $bots[$id]['name'];
    $colorCode = getColorCodeFromString($name);

    $chartDatasetsString .= '
    {
        label: "' . $name . '",
        backgroundColor: \'#' . $colorCode . '\',
        borderColor: \'#'. $colorCode . '\',
        data: [' . implode(',', $chartsData) . '],
        fill: false,
        hidden: ' . ($latestEloRating < 2200 ? 'true' : 'false') . ',
    },';
}

?>

<script type="text/javascript" src="./js/utils.js"></script>
<script type="text/javascript" src="./js/Chart.bundle.min.js"></script>
<div id="eloRatingsChartDiv"><canvas id="eloRatingsChart"></canvas></div>
<script>

var ctx = document.getElementById("eloRatingsChart").getContext('2d');

var config = {
    type: 'line',
    data: {
        labels: [<?php echo($chartLabelsString);?>],
        datasets: [<?php echo($chartDatasetsString);?>]
    },
    options: {
        responsive: true,
        title: {
            display: true,
            text: 'ELO ratings over time'
        },
        tooltips: {
            mode: 'index',
            intersect: false,
        },
        hover: {
            mode: 'nearest',
            intersect: true
        },
        scales: {
            xAxes: [{
                display: true,
                scaleLabel: {
                    display: true,
                    labelString: 'Date'
                }
            }],
            yAxes: [{
                display: true,
                scaleLabel: {
                    display: true,
                    labelString: 'ELO rating'
                }
            }]
        }
    }
};

new Chart(ctx, config);

$(document).ready(function() {
    $("#eloRatingsChart").outerHeight($(window).height() - $("#eloRatingsChart").offset().top - Math.abs($("#eloRatingsChart").outerHeight(true) - $("#eloRatingsChart").outerHeight()) - 100);
});

</script>
