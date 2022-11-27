<?php
//===============================================
// DB connection & other settings
//===============================================
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';
require_once("includes/generalFunctions.php");

//===============================================
// CORE FUNCTIONS
//===============================================

function techo($str) {
	echo $str;
}

// Set the page title
$pageTitle = PAGE_TITLE;
if (isset($_GET['action']) && ($_GET['action'] == 'botDetails') && isset($_GET['bot'])) {
	// special title for bot profile page
	$pageTitle = trim(htmlspecialchars($_GET['bot'])).': '.$pageTitle;
}

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $pageTitle; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="canonical" href="http://sscaitournament.com/" />
	<meta property="og:title" content="<?php echo PAGE_TITLE; ?>"/>
	<meta property="og:image" content="http://sscaitournament.com/images/page_thumbnail_image.png"/>
	<meta property="og:description" content="<?php echo PAGE_TITLE; ?>"/>
	<meta name="title" content="<?php echo PAGE_TITLE; ?>" />
	<meta name="description" content="<?php echo PAGE_TITLE; ?>" />
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<link rel="icon" type="image/png" href="./images/favicon.ico" />
	<link rel="stylesheet" href="./css/screen.css?ver=3.7" TYPE="text/css" media="screen" />
	<!--[if lte IE 7]><link rel="stylesheet" type="text/css" href="./css/screen_ie.css" /><![endif]-->

	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

	<!-- Sticky Table Headers -->
	<script src="./includes/stickyheader.jquery.js"></script>

	<!-- Google Analytics JS -->
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-30529098-1']);
	  _gaq.push(['_trackPageview']);
	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>

	<!-- Refreshing the match schedule (jQuery) -->
	<?php if (!isset($_GET['action'])) { ?>
	<!-- Refresh following matches -->
	<script  type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js"></script>
	<script type="text/javascript">
		var auto_refresh = setInterval(
		function (){
			$('#schedule').load('schedule.php').fadeIn("slow");
		}, 60000); // refresh every 60000 milliseconds
	</script>
	<?php } ?>

	<?php if (checkIsActionOneOf(['tutorial', 'tutorialCpp'])) { ?>
	<!-- Source Code Highlighting -->
	<link href="./css/prism.css" rel="stylesheet" />
	<script src="./includes/prism.js"></script>
	<?php
	}
	?>

	<!-- Bot Table Sorting -->
	<script src="./includes/sorttable.js"></script>

    <!-- reCAPTCHA by Google -->
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <?php /*
    <!-- Coin-Hive.com -->
    <script src="https://coin-hive.com/lib/coinhive.min.js"></script>
    */ ?>

</head>
<body>
	<div id="header">
		<h1><?php techo(PAGE_TITLE);?></h1>
		<div><?php techo('Founded at Comenius University in Bratislava &amp; Czech Technical University in Prague');?></div>

		<div id="social_buttons">
			<table>
				<tr>
					<td>
						<div class="fb-like" data-href="http://www.sscaitournament.com/" data-send="false" data-layout="box_count" data-width="55" data-show-faces="false" data-colorscheme="dark" data-font="arial"></div>
					</td>
				</tr>
			</table>
		</div>

		<div></div>
	</div>
	<div id="wrapper">
		<div class="table-row">
			<div id="menu">
				<ul>
					<li id="menuDiscord"><img src="./images/icon_discord.png" alt="" /><a target="_blank" href="https://discordapp.com/invite/w9wRRrF">Discord</a></li>
					<li id="menuVideos"><img src="./images/icon_youtube.png" alt="" /><a target="_blank" href="http://www.youtube.com/user/certicky/">YouTube Channel</a></li>
					<li id="menuTwitter"><img src="./images/icon_twitter.png" alt="" /><a target="_blank" href="http://twitter.com/sscaitournament">Twitter</a></li>
					<li id="menuDiscussion"><img src="./images/icon_fb.png" alt="" /><a target="_blank" href="http://www.facebook.com/groups/bwapi/">Facebook Group</a></li>
				</ul>

				<div id="patreon-button">
    				<a href="https://www.patreon.com/sscait" title="Support SSCAIT on Patreon. Click for more info.">
    					<img src="./images/patreon.png" alt="Support us on Patreon" />
    				</a>
				</div>

				<div>&nbsp;</div>

				<ul>
					<li<?php if (!isset($_GET['action'])) echo ' class="selected"';?>><a href="./" title="<?php techo("Tournament Home Page"); ?>"><?php techo("Home"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['scoresCompetitive', 'scores', 'voteForPlayers', 'botDetails'])) echo ' class="selected"';?>><a href="./index.php?action=<?php if ($GLOBALS["competitivePhase"]) {echo 'scoresCompetitive';} else {echo 'scores';} ?>" title="<?php techo("Bots &amp; Results"); ?>"><?php techo("Bots &amp; Results"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['submit'])) echo ' class="selected"';?>><a href="./index.php?action=submit" title="<?php techo("Log In or Submit your Bot"); ?>"><?php techo("Log In &amp; Submit Bots"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['tutorial', 'tutorialCpp'])) echo ' class="selected"';?>><a href="./index.php?action=tutorial" title="<?php techo("Bot Creation Tutorial"); ?>"><?php techo("Tutorial"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['downloads'])) echo ' class="selected"';?>><a href="./index.php?action=downloads" title="<?php techo("Downloads &amp; Links"); ?>"><?php techo("Downloads &amp; Links"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['rules'])) echo ' class="selected"';?>><a href="./index.php?action=rules" title="<?php techo("Rules"); ?>"><?php techo("Rules"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['achievements'])) echo ' class="selected"';?>><a href="./index.php?action=achievements" title="<?php techo("Achievements &amp; Portraits"); ?>">Achievements &amp; Portraits</a></li>
					<li<?php if (checkIsActionOneOf(['maps'])) echo ' class="selected"';?>><a href="./index.php?action=maps" title="<?php techo("Map Collection"); ?>"><?php techo("Maps"); ?></a></li>
					<!--<li<?php if (checkIsActionOneOf(['plagiarism'])) echo ' class="selected"';?>><a href="./index.php?action=plagiarism" title="<?php techo("Plagiarism Checking"); ?>"><?php techo("Plagiarism Check"); ?></a></li>-->
					<li<?php if (checkIsActionOneOf(['presskit'])) echo ' class="selected"';?>><a href="./index.php?action=presskit" title="<?php techo("Press Kit"); ?>"><?php techo("Press Kit"); ?></a></li>
					<li<?php if (checkIsActionOneOf(['contact'])) echo ' class="selected"';?>><a href="./index.php?action=contact" title="<?php techo("Contact us"); ?>"><?php techo("Contact"); ?></a></li>
				</ul>

				<div>
				<?php techo("Tournament Years"); ?>:
				</div>
				<ul>
					<li><a target="_blank" href="https://liquipedia.net/starcraft/SSCAIT2021" title="Student StarCraft AI Tournament 2021/22">SSCAIT 2021/22</a></li>
					<li<?php if (checkIsActionOneOf(['2020'])) echo ' class="selected"';?>><a href="./index.php?action=2020" title="Student StarCraft AI Tournament 2020/21">SSCAIT 2020/21</a></li>
					<li<?php if (checkIsActionOneOf(['2019'])) echo ' class="selected"';?>><a href="./index.php?action=2019" title="Student StarCraft AI Tournament 2019/20">SSCAIT 2019/20</a></li>
					<li<?php if (checkIsActionOneOf(['2018'])) echo ' class="selected"';?>><a href="./index.php?action=2018" title="Student StarCraft AI Tournament 2018/19">SSCAIT 2018/19</a></li>
					<li<?php if (checkIsActionOneOf(['2017'])) echo ' class="selected"';?>><a href="./index.php?action=2017" title="Student StarCraft AI Tournament 2017/18">SSCAIT 2017/18</a></li>
					<li<?php if (checkIsActionOneOf(['2016'])) echo ' class="selected"';?>><a href="./index.php?action=2016" title="Student StarCraft AI Tournament 2016/17">SSCAIT 2016/17</a></li>
					<li<?php if (checkIsActionOneOf(['2015'])) echo ' class="selected"';?>><a href="./index.php?action=2015" title="Student StarCraft AI Tournament 2015/16">SSCAIT 2015/16</a></li>
					<li<?php if (checkIsActionOneOf(['2014'])) echo ' class="selected"';?>><a href="./index.php?action=2014" title="Student StarCraft AI Tournament 2014/15">SSCAIT 2014/15</a></li>
					<li<?php if (checkIsActionOneOf(['2013'])) echo ' class="selected"';?>><a href="./index.php?action=2013" title="Student StarCraft AI Tournament 2013/14">SSCAIT 2013/14</a></li>
					<li<?php if (checkIsActionOneOf(['2012'])) echo ' class="selected"';?>><a href="./index.php?action=2012" title="Student StarCraft AI Tournament 2012/13">SSCAIT 2012/13</a></li>
					<li<?php if (checkIsActionOneOf(['2011'])) echo ' class="selected"';?>><a href="./index.php?action=2011" title="Student StarCraft AI Tournament 2011"><?php techo("SSCAIT 2011"); ?></a></li>
				</ul>
			</div>

			<div id="contentWrapper">
				<div id="content">
			<?php
                if (!empty($_REQUEST['userNotice'])
                    && checkValidateUserNotice($_REQUEST['userNotice'])
                ) {
                    echo(urldecode($_REQUEST['userNotice']));
                }

            $allowed = [
                "home",
                "blog",
                "tutorial",
                "tutorialCpp",
                "results",
                "scores",
                "scoresCompetitive",
                "downloads",
                "contact",
                "maps",
                "submit",
                "rules",
                "botDetails",
                "2011",
                "2012",
                "2013",
                "2014",
                "2015",
                "2016",
                "2017",
                "2018",
                "2019",
                "2020",
                "achievements",
                "presskit",
                "voteForPlayers",
                "eloChartBig",
            ];
            if ((checkIsActionOneOf($allowed))) {
					require("./includes/".$_GET['action'].".php");
				} else {
					require("./includes/home.php");
				}
			?>
				</div>
			</div>
			<?php /* DISABLED */ if (FALSE && ((!isset($_GET['action'])) || ($_GET['action'] == ""))) { ?>
			<div id="rightPanel" class="">
				<table><tr><td style="padding-bottom: 15px;"><script type="text/javascript"><!--
                                google_ad_client = "ca-pub-4086065973765901";
                                /* SSCAI Sidebar Half-Banner */
                                google_ad_slot = "9632578466";
                                google_ad_width = 234;
                                google_ad_height = 60;
                                //-->
                                </script>
                                <script type="text/javascript"
                                src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                                </script>
				</td></tr>
                                <script type="text/javascript"
                                src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                                </script>
                                <script type="text/javascript" src="includes/advertisement2.js"></script>
				</table>
			</div>
			<?php } ?>
		</div>
		<div id="footer">
			<a target="_blank" rel="nofollow" href="http://www.blizzard.com/games/sc/">Starcraft</a> and <a target="_blank" rel="nofollow" href="http://www.blizzard.com/games/sc/">Starcraft: Broodwar</a> are trademarks of <a target="_blank" rel="nofollow" href="http://www.blizzard.com">Blizzard Entertainment</a>. The tournament uses a modification of <a target="_blank" href="http://webdocs.cs.ualberta.ca/~cdavid/starcraftaicomp/tm.shtml">Tournament management software</a> developed on University of Alberta. The content on this page is released under <a href="https://creativecommons.org/licenses/by-nc/4.0/" target="_blank">Creative Commons license 4.0</a>.
			<span style="float: right"><a href="https://www.iubenda.com/privacy-policy/61172162" class="iubenda-white iubenda-embed " title="Privacy Policy">Privacy Policy</a> <script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script></span>
			<div style="clear: both;"></div>
		</div>
	</div>

</body>
</html>

<?php
function checkIsActionOneOf($possibleActions)
{
    if (empty($_GET['action'])) {
        return false;
    }

    return in_array($_GET['action'], $possibleActions);
}
