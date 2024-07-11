<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';
?>

<h2>Rules</h2>
<h3>Participants</h3>
<div>
Anyone can submit a bot and play. Participants <b>do not need</b> to be students
any more (even though individual students usually have their own, easier category in
the annual tournaments).

<h4>Ladder:</h4>
There are no special requirements for participating in the ladder at the moment. However, in order to help the growing StarCraft AI ecosystem, authors are encouraged to create a page about their bot in the <a href="https://liquipedia.net/starcraft/Category:Bots" target="_blank">Bots section on Liquipedia</a> (see instructions below). It will also be added to their bot's SSCAIT profile page (<a href="https://sscaitournament.com/index.php?action=botDetails&bot=PurpleWave" target="_blank">example</a>).

<h4>Tournaments:</h4>

    <ul>
        <li>
            During (yearly) tournaments, only one submission per author is allowed due to the possibility of collusion and other shenanigans. 
            In order to participate in the yearly tournament, every bot <b>must have its own page</b> on Liquipedia. The Liquipedia page should have the 'Bots' category (e.g. a line '[[Category:Bots]]' near the end of the page while editing the page) so that it is linked from the <a href="https://liquipedia.net/starcraft/Category:Bots" target="_blank">Bots category</a> list. The page must contain the <b>names of all the programmers</b> who made the bot, in the 'programmers' variable. If the bot is <b>based on some other bot</b>, or is based on or uses <b>other bot-related projects/libraries</b> apart from BWAPI, this also needs to be disclosed on the page (in the 'framework' variable, or the 'wrapper'/'terrain_analysis' variable(s) respectively). You should use the <a href="https://liquipedia.net/starcraft/Template:Infobox_bot" target="_blank">Infobox_bot template</a> when creating that page.
        </li>
        <li>
            If you have worked on more than one bot that's active in SSCAIT, please make sure to inform the SSCAIT admins by email (<a href="mailto:<?php echo $GLOBALS["ADMIN_EMAIL"]; ?>"><?php echo $GLOBALS["ADMIN_EMAIL"]; ?></a>) or directly at least one week before the tournament starts, so we know which bot to keep enabled. Another option is to just submit an empty ZIP file which would disable a given entry automatically. If it gets discovered that multiple bots written by the same author end up participating in the tournament, they might all get disqualified!
        </li>
        <li>
            Also please note the new rule about <b>submitting clones of existing bots</b> below.
        </li>
    </ul>
</div>

<h3>Games</h3>
<ul>
	<li>All the games are 1v1. Game type is set to Melee.</li>
	<li>StarCraft Brood War version 1.16.1 will be used for all games.</li>
	<li>
		<b>Loss:</b> A bot loses immediately under these conditions:
			<ul>
			<li>If it loses all the buildings.</li>
			<li>If it crashes.</li>
			<li>If it slows down the game significantly. Every game frame takes some real-world time, depending on what bot is doing. The bot loses if he has:
				<ul>
				<li>More than 1 frame longer than 10 seconds, or</li>
				<li>more than 10 frames longer than 1 second, or</li>
				<li>more than 320 frames longer than 85 milliseconds.</li>
				</ul>
			</li>
			</ul>
	</li>
	<li><b>Victory:</b> The bot wins immediately if its opponent loses (see above).</li>
	<li><b>Draw:</b> Draw results are no longer possible.</li>
	<li><b>Time limit:</b> The game ends automatically either after 90 in-game minutes (86400 frames) or when no unit dies for 5 real-world minutes. When any of that happens, the win is assigned to the bot that has higher in-game kills+razings score, computed as <span style="font-family: monospace">BWAPI::Player::getKillScore() + BWAPI::Player::getRazingScore()</span>;
	<li><b>Speed:</b> By default, the games run at speed=20 (which is approximately 30% faster than the <i>"Fastest"</i> speed setting). Specific periods of the game are dynamically accelerated to maximum speed (speed=0).</li>
	<li>We stream every match live on our <a href="https://sscaitournament.com/">home page</a> or at <a target="_blank" href="http://twitch.tv/sscait">Twitch.tv</a>. The replays are saved on the server and will be published after the tournament.</li>
	<li>Maps are selected randomly from our <a href="./index.php?action=maps">map pool</a>.</li>
	<li>Complete map vision, or any other flags/cheats are forbidden.</li>
	<li>If the match result is evaluated incorrectly for some unknown reason, admins can fix it based on the replay file (replay files are accessible for users after logging in).</li>
</ul>

<h3>Bots</h3>
<div>Here, we're getting a bit more technical.</div>
<ul>
	<li>You need to submit a source code AND compiled bot (we will never publish or use your sources - we need them only for automated plagiarism check).
	We accept bots coded either as standard C++ BWAPI AI Modules or Proxy Bots or in JAVA (using <a href="http://sscaitournament.com/index.php?action=tutorial">BWMirror</a>
	or <a href="http://code.google.com/p/jnibwapi/">JNIBWAPI</a>). C++ bots need to be compiled as <b>DLL</b> or <b>EXE</b> files, Java bots as <b>runnable JAR</b>.</li>
	<li>Each bot needs to be submitted with a compatible BWAPI.dll file (follow the on-screen instructions while submitting the bot).</li>
	<li>The BWAPI.dll must be one of the versions supported by the tournament. The list of supported BWAPI versions expands over time and includes for example 3.7.4, 3.7.5, 4.1.0-beta, 4.1.1, 4.1.2 4.2.0 and 4.4.0.</li>
	<li>The BWAPI.dll that you submit, must match one of the following MD5 hashes:
        <ul>
            <li>1364390d0aa085fba6ac11b7177797b0</li>
            <li>2f6fb401c0dcf65925ee7ad34dc6414a</li>
            <li>4814396fba36916fdb7cf3803b39ab51</li>
            <li>5d5128709ba714aa9c6095598bcf4624</li>
            <li>5e590ea55c2d3c66a36bf75537f8655a</li>
            <li>6e940dc6acc76b6e459b39a9cdd466ae</li>
            <li>cf7a19fe79fad87f88177c6e327eaedc</li>
        </ul>
    </li>
    <li>Using BWTA or similar libraries is allowed and encouraged. If you have some special requirements (external programs, libraries, etc.), <a href="./index.php?action=contact">let us know</a>. </li>
	<li>The bots need to run on 32bit Windows 7 machines.</li>
    <li><b>Race:</b> All three races (Terran, Zerg, Protoss) are allowed. The 'Random' race is allowed in the ladder and round robin phase of the tournament, but Random bots will not be allowed in the elimination tournament phase. If you'd like your bot to continue playing in the second (elimination) phase of the tournament, your entry must have picked a specific race <b>*before*</b> the tournament starts.</li>
    <li>Bots that behave suspiciously, don't run, or lag too much (e.g. lagging client based bots that the tournament module can't detect) can be disqualified.</li>
    <li>The SSCAIT admins reserve the right to disqualify any bots that are implementation-wise too similar to previous entries (e.g. clones and forks) without adding enough new / original functionality to the table.</li>
    <li>Bots are <b>NOT</b> allowed to:
        <ul>
            <li>intentionally crash StarCraft</li>
            <li>pause the game, games in which a bot pauses the game will be counted as a loss</li>
            <li>spam the in-game console</li>
        </ul>
    </li>
    <li>The following StarCraft bugs/tricks are <b>permitted</b>:
        <ul>
            <li>Plague on interceptor</li>
            <li>Units pressed through</li>
            <li>Drops to defuse mines</li>
            <li>Mineral walk</li>
            <li>Manner Pylon</li>
            <li>Lurker hold position</li>
            <li>Observer over turret</li>
            <li>Stacking air units</li>
        </ul>
    </li>
    <li>All other bugs/exploits are <b>forbidden</b>. Bots caught attempted these exploits will be disqualified. This includes but is not limit to:
        <ul>
            <li>Flying drones and templars</li>
            <li>Terran sliding buildings</li>
            <li>Intentional stacking of ground units</li>
            <li>Allied mines</li>
            <li>Gas walk (in all cases including: to get through blocked entrances or ramps; to attack any units)</li>
        </ul>
    </li>
    <li>
        If you copy other bots or use IP/files/source code/logic/techniques etc from other bots, you must familiarize yourself with their licenses and ensure that you are not infringing their licenses. Copying other bot(s) is allowed, so long as it does not infringe their licenses and so long as you modify their logic or if you use/wrap it without modifying it and add some of your own logic on top of it, similar to how MegaBot used Skynet/Xelnaga/NUSBot in year 2017, or wrapping/modifying Randomhammer/UAlbertaBot/CommandCenter etc. If you do something like this, you must provide the source code and compilation instructions etc of the bots that you use, so that we can compile them. We decided that to foster research it is best to have the next generation of programmers stand on the shoulders of giants, rather than re-invent the wheel. We encourage authors to take code from old years of this competition and improve it.
        If you copy a bot, please uphold the spirit of competition and ensure you make significant modification or addition before you submit it. We don?t want multiple apparently near-identical copies of the same bot competing!
        Additionally, please contact the original bot (in case it has been updated at least once during the past year) author and ask them for permission to upload it.
    </li>
</ul>

<h3>Writing and Reading Files</h3>
<div>
<ul>
	<li>You have read access to folder <b>'bwapi-data/read/'</b> and write access to folder <b>'bwapi-data/write/'</b>, both of which are in the StarCraft root location (where StarCraft.exe is).
	After every match, the contents of the <b>write</b> directory is copied to the <b>read</b> directory with auto-overwrite. Your bot will be placed in <b>'bwapi-data/AI/'</b> folder.</li>
	<li>Any reading or writing of other directories is forbidden! You have a 100MB limit for all the files in the read/ directory. If you go over this, you risk being disqualified.</li>
	<li>Your bot should never change current working directory (CWD). This might cause the tournament manager to think it's crashed and assign a loss.</li>
	<li>You can manually upload the contents of <b>'bwapi-data/read/'</b> folder via <a href="https://sscaitournament.com/index.php?action=submit">bot submission interface</a>, along with new versions of your bot.</li>
	<li>The contents of the read/ and write/ folders are only accessible to you! No other bot author can view or download the contents of those two folders!</li>
</ul>
</div>



