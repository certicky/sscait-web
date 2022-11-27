<h2><?php techo('Map Collection');?></h2>
<div>
For every game of the tournament, a random map will be selected from our <a href="./files/sscai_map_pack.zip">collection</a>.
The maps are selected from the <a target="_blank" href="http://wiki.teamliquid.net/starcraft/Maps_Used_During_Years">list of most used 
tournament maps</a> from recent few years.
</div>

<div>
Our map pool contains the majority of maps used in <a target="_blank" href="http://skatgame.net/mburo/sc2011/rules.html">AIIDE Starcraft AI Competition</a> and <a target="_blank" href="http://94.26.36.43/bwapi_bot_automation_central_server/bots_stats.php">BWAPI Bot Ladder</a>.
</div>

<div style="margin-top: 2ex;">
<?php
if ($handle = opendir('./images/map_collection')) {

    /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
        if (($entry != ".") && ($entry != "..")) {
			if (stripos($entry,"_thumbnail") !== FALSE ) {
				echo '<a class="map_thumbnail" target="_blank" href="./images/map_collection/'.str_replace("_thumbnail","",$entry).'"><div>'.str_replace(".jpg","",str_replace("_thumbnail","",$entry)).'</div><img src="./images/map_collection/'.$entry.'" alt="" /></a>';
			}
        
		}
    }

    closedir($handle);
}
?>
</div>
