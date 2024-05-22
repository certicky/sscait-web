<?php

if (!empty($_REQUEST['debugMode'])) {
    $GLOBALS['debugMode'] = true;
}

// retrieves the actual user IP from cloud flare
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

require_once(dirname(__FILE__) . '/../settings_server.php');

$dsn = "mysql:host=$db_host;dbname=$db_database;charset=utf8";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT		 => false,
];
$pdo = new PDO($dsn, $db_username, $db_password, $opt);

define('START_ELO', 2000);

// displays the error and exit
function fatalError($text)
{
    global $GLOBALS;

    if (!empty($GLOBALS['debugMode'])) {
        die("\nError: " . $text . "\n");
    }

    die('');
}

// get bot alias based on its name, if it's defined
function getBotAlias($botName) {

    // list of aliases
    $botAliases = array(
        "Marian Devecka" => "KillerBot",
        "Martin Rooijackers" => "LetaBot",
        "Chris Coxe" => "ZZZKBot",
        "Tomas Vajda" => "XIMP",
        "Matej Istenik" => "Dementor",
        "Dave Churchill" => "UAlbertaBot",
        "ICELab" => "ICEBot",
        "legacy" => "LegacyBot",
        "Soeren Klett" => "W.O.P.R.",
        "Hao Pan" => "Halo",
        "Fresh Meat" => "Halo",
        "Aurelien Lermant" => "GarmBot",
        "Bjorn P Mattsson" => "Loki AI",
        "Sijia Xu" => "Overkill",
        "Flash" => "Flash_(Bot)",
        "NiteKatT" => "BGH_Bot",
        "NiteKatP" => "BGH_Bot",
        "XIAOYICOG2019" => "XiaoYi",
        "Simon Prins" => "Tyr",
        "Andrew Smith" => "Skynet",
        "Florian Richoux" => "AIUR",
        "Yuanheng Zhu" => "Juno",
        "PurpleSwarm" => "PurpleWave",
        "PurpleSpirit" => "PurpleWave",
        "PurpleDestiny" => "PurpleWave",
        "Bryan Weber" => "CUNYBot",
        "CherryPiSSCAIT2017" => "CherryPi",
        "Proxy" => "Proxybot",
        "Dragon" => "DragonBot",
        "Lukas Moravec" => "Jonathan",
        "krasi0P" => "Krasi0bot",
        "Infested Artosis" => "Infested_Artosis",
        "Crona" => "BananaBrain",
        "Terminus" => "BananaBrain",
        "Brainiac" => "BananaBrain",
        "adias" => "SAIDA",
        "Sparks" => "McRave",
        "Randomhammer" => "Steamhammer",
        "MegaBot2017" => "MegaBot",
        "NUS Bot" => "NUS-Bot",
        "Oyvind Johannessen" => "MadMix",
        "WOPR Z" => "W.O.P.R.",
        "ZZZBot" => "ZZZKBot",
        "CherryPi 2018 AIIDE MOD" => "CherryPi",
        "LetaBot SSCAI 2015 Final" => "LetaBot",
        "LetaBot CIG 2016" => "LetaBot",
        "LetaBot AIIDE 2016" => "LetaBot",
        "LetaBot AIIDE 2017" => "LetaBot",
        "LetaBot CIG 2017" => "LetaBot",
        "CherryPiSSCAIT2017 dupl" => "CherryPi"
    );

    if (isset($botAliases[$botName])) $botName = $botAliases[$botName];
    return $botName;
}

function isLiquipediaPageNameAllowed($botName) {
    $forbidden = array(
        // note: these need to be lowercase
        "proxy",
        "flash",
        "dragon",
        "legacy"
    );
    return !in_array(strtolower($botName), $forbidden);
}

/**
 * @param int $offset indicates how many games in the future to count, where 0 means the next game, 1 means
 *                    the game after the next one and so on
 *
 * @return array
 */
function getNextGameDetails($offset = 0)
{
    if ($offset < 0) {
        fatalError('Invalid $offset in getNextGameId()');
    }

    $res = mysql_query("SELECT * FROM games WHERE result='unfinished' ORDER BY game_id ASC LIMIT 10;");
    if (mysql_num_rows($res) <= 0) {
        fatalError('Next game not found');
    }

    $index = 0;
    while ($l = mysql_fetch_assoc($res)) {
        if ($index >= $offset) {
            $bot1 = getBotDetailsById($l['bot1']);
            $bot2 = getBotDetailsById($l['bot2']);

            return [
                $l['game_id'],
                $bot1['full_name'],
                $bot2['full_name'],
            ];
        }

        $index++;
    }
}

function getBotDetailsById($botId)
{
    $res = mysql_query("SELECT * FROM fos_user WHERE id='" . mysql_escape_string($botId) . "';");
    $row = mysql_fetch_assoc($res);

    if (empty($row)) {
        fatalError("Bot with id " . $botId . " not found");
    }

    return $row;
}

function getEloRatings()
{
    global $eloRatingsRemoteURL, $eloRatingsBASILRemoteURL;

    $eloRatings = [];
    $iccupFormula = [];
    $iccupRanks = [];
    $note       = '';

    if (empty($eloRatingsRemoteURL)) {
        return [$eloRatings, $iccupFormula, $iccupRanks, $note];
    }

    $contents = getWebPageContents($eloRatingsRemoteURL);

    if (preg_match_all('/td bgcolor.+?>(.+?)<.+?<td>(\d*?)<\/td>\s*?<td>([^<]*?)<\/td>\s*?<td>(\d*?)<\/td>\s*?<\/tr/s', $contents, $matches)) {
        $index = 0;
        foreach ($matches[1] as $botName) {
            $eloRatings[$botName] = $matches[4][$index];
            $iccupFormula[$botName] = $matches[2][$index];
            $iccupRanks[$botName] = $matches[3][$index];

            $index++;
        }
    }

    if (preg_match('/<p.+?>(.+?)<\/p>/s', $contents, $matches)) {
        $note = $matches[1]
                . '<br>Bot stats loaded from:&nbsp;<a href="' . $eloRatingsRemoteURL . '" target="_blank">'
                . $eloRatingsRemoteURL
                . '</a> (original url by MicroDK: http://scbw.holdorf.dk/sscait/ratings.html). '
                . '<br> *SSCAIT ranking is based on the ICCUP ranking system.';

        if (!empty($eloRatingsBASILRemoteURL)) {
            $note .= '<br><b>ELO ratings loaded from BASIL</b>: ' . $eloRatingsBASILRemoteURL;
        }

        $note = str_replace('href="crosstable.html"', 'href="https://cachedsscaitscores.krasi0.com/eloRatings/crosstable.php"', $note);
        $note = str_replace('href="crosstable.php"', 'href="https://cachedsscaitscores.krasi0.com/eloRatings/crosstable.php"', $note);
    }

    // Now, try fetching the more accurate ratings from BASIL and use those if available
    if (!empty($eloRatingsBASILRemoteURL)) {
        $json         = getWebPageContents($eloRatingsBASILRemoteURL);
        $basilDataArr = json_decode($json, true);
        if (!empty($basilDataArr)) {
            foreach ($basilDataArr as $botEntry) {
                if (!empty($botEntry['played'])) {
                    $eloRatings[$botEntry['botName']] = $botEntry['rating'];
                }
            }
        }
    }

    return [$eloRatings, $iccupFormula, $iccupRanks, $note];
}

function fetchSingleByQuery($query, $orNull = false)
{
    $result = mysql_query($query);
    if (!$result) {
        fatalError("Invalid query: " . $query);
    }

    $row = mysql_fetch_row($result);

    if (empty($row)) {
        if ($orNull) {
            return null;
        }

        fatalError("No result returned by " . $query);
    }

    return $row[0];
}

function fetchMultipleByQuery($query, $orNull = false)
{
    $result = mysql_query($query);
    if (!$result) {
        fatalError("Invalid query: " . $query);
    }

    $row = mysql_fetch_row($result);

    if (empty($row)) {
        if ($orNull) {
            return null;
        }

        fatalError("No result returned by " . $query);
    }

    return $row;
}

function redirectToUrl($urlWithoutQueryParams, $queryParams = [])
{
    $urlFinal = $urlWithoutQueryParams . '?';

    foreach ($queryParams as $key => $value) {
        $urlFinal .= $key . '=' . $value . '&';
    }
    ?>
    <script type="text/javascript">
        window.location.href = '<?php echo($urlFinal); ?>';
    </script>
    <?php

    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

function checkStringContains($string, $needle)
{
    return strpos($string, $needle) !== false;
}

function checkValidateUserNotice($userNotice)
{
    // it must contain a div or a span
    if (!checkStringContains($userNotice, '<div')
        && !checkStringContains($userNotice, '<span')
    ) {
        return false;
    }

    // no script tags are allowed in order to avoid XSS
    if (checkStringContains($userNotice, '<script')) {
        return false;
    }

    return true;
}

function getWebPageContents($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function getTextWithClickableLinks($s,$nofollow=false) {
    $bonusHTML = '';
    if ($nofollow) $bonusHTML = 'rel="nofollow"';
    return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank" '.$bonusHTML.' target="_blank">$1</a>', $s);
}

function getColorCodeFromString($str) {
    $code = dechex(crc32($str));
    $code = substr($code, 0, 6);
    return $code;
}

function validateReCAPTCHA($userIP = '')
{
    if (empty($_REQUEST['reCAPTCHAResponse'])) {
        return false;
    }

    $reCAPTCHAResponse = base64_decode($_REQUEST['reCAPTCHAResponse']);

    $url       = 'https://www.google.com/recaptcha/api/siteverify';
    $arguments = 'secret=6LdtdCwUAAAAAOPaSsIbjmjw_mjEJK0XPLsNJnw_&response='
                 . $reCAPTCHAResponse
                 . (!empty($userIP) ? 'remoteip=' . $userIP : '');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arguments);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);

    if (empty($response)) {
        return false;
    }

    $responseArray = json_decode($response, true);

    if (empty($responseArray['success'])) {
        return false;
    }

    return true;
}

// super simple caching class
class SimpleCache {
    protected $path = null;
    protected $durationSeconds = null;

    function __construct ( $durationSeconds = 60) {
        $this->path = $GLOBALS["CACHE_FOLDER_WITHOUT_SLASH"].'/';
        $this->durationSeconds = $durationSeconds;
    }

    function get( $id, $durationSeconds = -1 ) {
        $file = $this->path . $id . '.cache';
        if ( $durationSeconds == -1 ) $durationSeconds = $this->durationSeconds;
        if (file_exists($file) && time() - filemtime($file) < $durationSeconds ) {
            return unserialize( file_get_contents($file) );
        } else {
            return null;
        }
    }

    function set( $id, $obj) {
        $file = $this->path . $id . '.cache';
        file_put_contents($file, serialize($obj));
    }
}
