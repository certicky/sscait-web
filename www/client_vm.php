<?php
// DB connection
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>SSCAIT Client Interface</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="refresh" content="30" >
	<link rel="stylesheet" href="./css/screen.css?ver=1.1" TYPE="text/css" media="screen" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<style type="text/css">
		* { background: rgb(30, 30, 30); font-size: 18pt;}
		* body { padding: 0; margin: 0; }
		img#client_header { padding: 0; margin: 0; }
		table { border-spacing: 0; }
		td { vertical-align: top; padding: 0 2px;}
		#following_matches { width: 500px; }
		div#time { color: #777777; display: block; position: absolute; background: transparent; left: 0; top: 270px; height: 30px; width: 1270px; text-align: right; font-size: 16pt; }
		span#time-clock { background: transparent; }
		div#time-last-game { color: #777777; background: transparent; position: absolute; left: 10px; top: 690px; font-size: 14pt; display: block; width: 600px; text-align: left; }
		div#right-bottom-corner { color: #777777; background: transparent; position: absolute; left: 670px; top: 690px; font-size: 14pt; display: block; width: 600px; text-align: right; }
		#patrons_list td.patrons_list_patron { padding: 5px 10px; }
	</style>

<script>
function startTime() {
    var today=new Date();
    var h=today.getHours();
    var m=today.getMinutes();
    var s=today.getSeconds();
    m = checkTime(m);
    s = checkTime(s);
    document.getElementById('time-clock').innerHTML = h+":"+m+":"+s;
    var t = setTimeout(function(){startTime()},500);
}
function checkTime(i) {
    if (i<10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
}
</script>

</head>
<body onload="startTime()">
<script>
function launchIntoFullscreen(element) {
  if(element.requestFullscreen) {
    element.requestFullscreen();
  } else if(element.mozRequestFullScreen) {
    element.mozRequestFullScreen();
  } else if(element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  } else if(element.msRequestFullscreen) {
    element.msRequestFullscreen();
  }
}
launchIntoFullscreen(document.documentElement);
</script>
<img id="client_header" src="./images/client_header.png" alt="" style="display: block;"/>
<?php
	$lastGameTime = "???";
	$sql = "SELECT datetime FROM `games` ORDER BY datetime DESC LIMIT 1";
	if ($line = mysql_fetch_assoc(mysql_query($sql))) {
		$lastGameTime = date('H:i:s',$line["datetime"]);
	}
?>
<div id="time-last-game">Last game finished at <?php echo $lastGameTime; ?> CET.</div>
<div id="right-bottom-corner">www.sscaitournament.com</div>
<div id="time">
	<span id="time-clock"></span>
</div>

<table style="width: 1280px;">
	<tr>
		<td>
<div id="following_matches_wrapper">Game schedule:</div>
<table id="following_matches">
<?php

include("./includes/getPortrait.php");
include("./includes/shortenName.php");

if (!$GLOBALS["eliminationBracketPhase"]) {
    $res = mysql_query("SELECT * FROM games WHERE result='unfinished' ORDER BY game_id ASC LIMIT 9;");
    $first = true;
    while ($l = mysql_fetch_assoc($res)) {
            $res1 = mysql_query("SELECT full_name,bot_race,id FROM fos_user WHERE id='".$l['bot1']."';");
            $res2 = mysql_query("SELECT full_name,bot_race,id FROM fos_user WHERE id='".$l['bot2']."';");
            $n1 = mysql_fetch_assoc($res1);
            $n2 = mysql_fetch_assoc($res2);
            if ($first) {$class='class="running"'; $first=false;} else {$class="";}

            echo '<tr '.$class.'><td class="host"><a href="./index.php?action=scores#'.urlencode($n1['full_name']).'">'.getSmallPortrait($n1['id'],$n1['bot_race'])." ".shortenName($n1['full_name'],13).'</a></td><td class="vs">vs.</td><td class="guest"><a href="./index.php?action=scores#'.urlencode($n2['full_name']).'">'.shortenName($n2['full_name'],13)." ".getSmallPortrait($n2['id'],$n2['bot_race']).'</a></td></tr>';
    }
}
?>
</table>
		</td>
		<td>
<div id="server_console_wrapper" style="border-bottom: solid 1px black;">
	Commentated streams:
</div>
<!--
<pre id="server_console" class="prettyprint prettyprinted">
</pre>
-->
<div id="server_console">
  <div style="margin-top: 15px; text-align: center;">Next upcoming stream with human commentary:</div>
  <div style="margin-top: 10px; text-align: center; color: #fff;">
	<?php if (date('m') == 1 || date('m') == 2) { ?>
		<b>Saturday & Sunday 20:00 CET</b>
    <?php } else { ?>
		<b>Sunday 20:00 CET</b>
	<?php } ?>
    <span style="font-size: 50%">(15:00 EDT)</span>
  </div>
  <div style="margin-top: 0px; text-align: center; font-size: 100%; color: #fff;">It will be streamed live at SSCAIT <span style="font-size: 100%; color: #FD0014">YouTube</span> channel.</div>

</div>

<div id="patrons_list_wrapper_supporters" style="border-bottom: solid 1px black;">
	Top Supporters:
</div>
<table id="patrons_list">

<?php
   $showPatrons = 3;

   // show top patrons (over 5000 cents)
   $res = mysql_query("SELECT * FROM supporters WHERE amount_monthly_cents >= 5000;");
   $patrons = array();
   while ($r = mysql_fetch_assoc($res)) {
       $patrons[] = '<tr><td class="patrons_list_patron">'.$r['full_name'].'</td></tr>';
   }
   shuffle($patrons);

   // add some lesser supporters if needed
   if (sizeof($patrons) < $showPatrons) {
        $res = mysql_query("SELECT * FROM supporters WHERE amount_monthly_cents < 5000 ORDER BY amount_monthly_cents DESC;");
        while ($r = mysql_fetch_assoc($res)) {
            if (sizeof($patrons) < $showPatrons) {
                $patrons[] = '<tr><td class="patrons_list_patron">'.$r['full_name'].'</td></tr>';
            }
        }
   }

   // display them
   $count = 0;
   foreach ($patrons as $p) {
       echo $p;
       $count += 1;
       if ($count >= $showPatrons) break;
   }

?>
</table>
<div id="patrons_list_wrapper" style="border-bottom: solid 1px black;">
	Like what we're doing? Support us on Patreon: <span style="background-color: none; font-size: 110%; font-weight: bold; font-variant: small-caps;">patreon.com/sscaitournament</span>
	<img style="float: right; height: 17pt;" src="./images/patreon.png" />
</div>

<script>
<?php if (!$GLOBALS["eliminationBracketPhase"]) { ?>
function readConsole(){
    jQuery.get('http://sscaitournament.com/client_console.txt',function(data){$("#server_console").text(data.replace(/\s\s+/g, ' ')); $("#server_console").scrollTop(1000); });
}
setInterval(readConsole,1500);
$( document ).ready(readConsole());
<?php } ?>
</script>

</body>
</html>
