
<div id="home-wrapper">
	<div id="home-stream-cell">

        <?php
        /*
        <!-- Notification -->
        <div style=" margin 2ex 0; padding: 1ex; background: #449D2D; color: white; font-size: 85%; border: solid 1px black; border-radius: 7px;">
        First round of SSCAIT 2017/18 (round-robin) is over. The results are <a style="color: #fff;" href="./index.php?action=2017">here</a>.<br>
        2nd round (double elimination bracket) is still in progress. Current state is <a style="color: #fff;" href="http://liquipedia.net/starcraft/SSCAIT2017">here</a>.<br>
        Bot submissions and match voting is enabled again.
        </div>
        */

        if ($GLOBALS["eliminationBracketPhase"]) {
            ?>
            <div style=" margin 2ex 0; padding: 1ex; background: #449D2D; color: white; font-size: 95%; border: solid 1px black; border-radius: 7px;">
                We're now trying to resolve the elimination bracket. The bot submission will be opened again soon.
            </div>
            <?php
        } else {
            if ($GLOBALS['competitivePhase']) {
                $resAll = mysql_query("SELECT count(game_id) AS cnt FROM games;");
                $res = mysql_query("SELECT count(game_id) AS cnt FROM games WHERE result!='unfinished';");
                $cntAll = mysql_fetch_assoc($resAll);
                $cnt = mysql_fetch_assoc($res);
                if ($cnt['cnt'] < $cntAll['cnt']) {
                ?>
                <div style=" margin 2ex 0; padding: 1ex; background: #449D2D; color: white; font-size: 95%; border: solid 1px black; border-radius: 7px;">
                	The competitive phase is now in progress.<br>
                	Games played so far: <b><?php echo $cnt['cnt'].' / '.$cntAll['cnt']; ?></b>
                </div>
                <?php
                } else {
                    ?>
                    <div style=" margin 2ex 0; padding: 1ex; background: #449D2D; color: white; font-size: 95%; border: solid 1px black; border-radius: 7px;">
                	All the <b><?php echo $cntAll['cnt']; ?></b> games of round robin phase have been played.
                	Soon, we'll start resolving the elimination bracket.
                	</div>
                    <?php
                }
            }
        }
		?>

		<!--
		<div style=" margin 2ex 0; padding: 1ex; background: #449D2D; color: white; font-size: 90%; border: solid 1px black; border-radius: 7px;">
			Round-robin phase of SSCAIT 2016 is over. The top 16 elimination bracket coming soon!<br/>
			In the meantime, enjoy some random games.<br/>
			Congrats to student division winners:
		    <ul>
		            <li>1st, 82pts: <b>Martin Rooijackers</b>, University of Maastricht (Netherlands)<br/></li>
		            <li>2nd, 63pts: <b>Wulibot</b>, University of Southern California (USA)<br/></li>
		            <li>3rd, 54pts: <b>Zia Bot</b>, UNIST (South Korea)<br/></li>
		    </ul>
			All the game results can be found <a href="http://sscaitournament.com/index.php?action=2016">here</a>.
		</div>
		-->

		<!-- Live stream -->
		<div id="streamFlash">
			<!-- Subscription  -->
			<div id="subscribe" style="margin-top: 2ex;">
				Our Discord server and YouTube channel:
			        <!--<a href="http://www.facebook.com/groups/bwapi/" id="sub_facebook" target="_blank" title="Join SC AI FB group">FB</a>-->
			        <a href="https://discordapp.com/invite/w9wRRrF" id="sub_discord" target="_blank" title="StarCraft AI Discord server">Discord</a>
			        <a href="http://www.youtube.com/user/certicky/" id="yt_channel" target="_blank" title="SSCAIT YouTube channel">YouTube</a>
		        </div>
			<!--<iframe style="margin: 0; padding: 0;"
			src="https://hitbox.tv/#!/embed/sscaitournament?autoplay=true" frameborder="0" allowfullscreen></iframe>-->
			<iframe style="margin: 0; padding: 0;"
                src="http://player.twitch.tv/?channel=sscait&parent=sscaitournament.com&parent=www.sscaitournament.com&muted=true"
                frameborder="0"
                scrolling="no"
                muted="true"
                allowfullscreen="true">
            </iframe>

		</div>
		<div id="streamScheduleWrapper">
			<div id="streamContainer">
				<div id="streamSchedule">
					<div id="schedule" style="text-align: left; padding: 0;">
						<?php include "schedule.php"; ?>
					</div>
				</div>
				<div id="streamNews">
					<div id="news_wrapper">News:</div>
						<!-- Twitter Feed Start -->
						<div id="twitter-widget"></div>
					    <script>
					      window.twttr = (function(d, s, id) {
					        var js, fjs = d.getElementsByTagName(s)[0],
					          t = window.twttr || {};
					        if (d.getElementById(id)) return t;
					        js = d.createElement(s);
					        js.id = id;
					        js.src = "https://platform.twitter.com/widgets.js";
					        fjs.parentNode.insertBefore(js, fjs);
					        t._e = [];
					        t.ready = function(f) {
					          t._e.push(f);
					        };
					        return t;
					      }(document, "script", "twitter-wjs"));
					    </script>
					    <script>
					      twttr.ready(function(twttr) {
					        twttr.widgets.createTimeline({
					          sourceType: "profile",
					          screenName: "sscaitournament"
					        }, document.getElementById("twitter-widget"), {
					          width: "275",
					          height: "275",
					          chrome: "transparent noheader nofooter noscrollbar noborders",
					          theme: "dark"
					        });
					      });
					    </script>
						<!-- Twitter Feed End -->
				</div>
			</div>
		</div>

		</div>

		<!-- About -->
		<div id="home-about-cell">
			<div id="aboutContainer">
				<div id="aboutText" style="display: table-cell; vertical-align: top; padding: 1ex;">
					<h2 style="margin-top: 0;">What is SSCAIT?</h2>
					<i>Student StarCraft AI Tournament</i> is an educational event, first held in 2011.
					It serves as a challenging competitive environment mainly for students (submissions by non-students are allowed too) of Artificial Intelligence and Computer Science.
					They are submitting the bots programmed in
					C++ or Java using <a target="_blank" href="http://code.google.com/p/bwapi/">BWAPI</a> to play 1v1 <a target="_blank" href="http://en.wikipedia.org/wiki/StarCraft">StarCraft</a> matches.
				</div>

			</div>

			<div>
				<div id="referenceText" style="display: table-cell; vertical-align: top; padding: 1ex; text-align: left;">
					<h2 style="margin-top: 0;">Referencing SSCAIT</h2>
					To reference SSCAIT in your publications, please cite:
					<div style="padding: 10px 0; font-size: 85%; text-align: left;">
						<?php include './includes/publicationsList.php'; ?>
					</div>

				</div>
			</div>

			<div>
				<div id="supportText" style="display: table-cell; vertical-align: top; padding: 1ex; text-align: left;">
					<h2 style="margin-top: 0;">Supporting us on Patreon</h2>

					SSCAIT is a non-profit project, but we're trying to help out young
					students interested in Game AI research.
					By <a href="https://www.patreon.com/sscait">supporting us on Patreon</a>, you're directly sponsoring two things:

					<ul style="">
						<li>The tournament itself (hardware, maintenance, hosting)</li>
						<li>Education of a few students and researchers in the group behind the event, called <a href="http://gas.fel.cvut.cz/" target="_blank">G&amp;S Research Group</a>. Your support allows a few enthusiastic students do Game AI research.</li>
					</ul>

					Here's the current list of our official supporters:
					<div style="font-style: italic; padding-bottom: 5px;">
					<?php
					   $res = mysql_query("SELECT * FROM supporters ORDER BY full_name ASC;");
					   $patrons = array();
					   while ($r = mysql_fetch_assoc($res)) {
					       if ($r['amount_monthly_cents'] >= 1000) {
    					       if ($r['link'] == "") {
    					           $patrons[] = $r['full_name'];
    					       } else {
    					           $patrons[] = '<a href="'.$r['link'].'" target="_blank">'.$r['full_name'].'</a>';
    					       }
					       }
					   }
					   echo join(", ",$patrons);
					?>
					</div>
					Thank you!

				</div>
			</div>

		</div>
</div>
