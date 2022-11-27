  <h2>Tutorial for creating a StarCraft bot</h2>
  <p>We will show you all the steps required to create a StarCraft bot, run it on your computer, and have it play on SSCAIT.</p>
  
  <h3>Introduction</h3>
  <p>Your bot will communicate with StarCraft using the Brood War API (BWAPI). BWAPI lets your bot play the game instead of a human player.</p>  
  <p>We will walk you through making a bot using the Java language on Windows. We'll briefly discuss other languages and operating systems you can use before getting back to the tutorial for Java on Windows.</p>
  
  <h4>Other ways to get started</h4>
  <p>Languages with good support include <a href="https://github.com/bwapi/bwapi">C++</a>, <a href="https://github.com/JavaBWAPI/jbwapi-scala-template">Scala</a>, <a href="https://github.com/JavaBWAPI/jbwapi-kotlin-template">Kotlin</a>, and <a href="https://github.com/JavaBWAPI/jbwapi-jruby-template">Ruby</a>. Python (<a href="https://github.com/neumond/pybrood">PyBrood</a>, <a href="https://github.com/TorchCraft/TorchCraft">TorchCraft</a>), <a href="https://github.com/satikcz/BWAPI-CLI">C#</a>, and <a href="https://github.com/Bytekeeper/rsbwapi">Rust</a> have BWAPI libraries but are less well-tested. The setup steps are mostly the same for all languages.</p>
  <p>StarCraft and BWAPI only run in Windows, but you can develop on other operating systems with some extra steps. You can run games or compile C++ bots using WINE, Docker, or virtual machines. <a href="https://github.com/basil-ladder/sc-docker">SC-Docker</a> runs BWAPI games on multiple platforms. <a href="https://github.com/bmnielsen/StardustDevEnvironment">StardustDevEnvironment</a> is for writing bots in Linux or MacOS. It uses <a href="https://github.com/OpenBW/openbw">OpenBW</a> which is an alternative version of StarCraft and BWAPI which is cross-platform, but not immediately compatible with bots compiled for StarCraft 1.16.1. StardustDevEnvironment lets you test locally on OpenBW and then compile a Windows binary for competitions like the SSCAI Tournament.</p>
  <p>And lastly, if you're interested in writing a bot in C++ on Windows, check out <a href="https://www.youtube.com/watch?v=FEEkO6__GKw">Dave Churchill's C++ bot tutorial</a>. You may still find most of this tutorial helpful, as the Java and C++ interfaces are very similar.</p>
    
  <h4>Getting help</h4>
  <p>If you get stuck in your StarCraft AI journey, check out the Troubleshooting section below, or visit the <a target="_blank" href="https://discordapp.com/invite/w9wRRrF">SSCAIT Discord chat room</a>. The community is very welcoming to new authors and eager to help.</p>

  <h3 id="setup">Java bot on Windows: Setup</h3>
  <ul><li>Download and unzip <a href="http://www.cs.mun.ca/~dchurchill/starcraftaicomp/files/Starcraft_1161.zip" target="_blank">StarCraft 1.16.1</a> from Memorial University, hosted with permission from Activision Blizzard. Newer versions of StarCraft like Remastered are incompatible with BWAPI.</li>
	<li>Download the <a href="https://sscaitournament.com/files/sscai_map_pack.zip">SSCAI map pack</a> and extract the included <code>sscai</code> directory into <code>Starcraft/maps/</code></li>
  <li>Install the <a href="http://www.cs.mun.ca/~dchurchill/starcraftaicomp/files/aiide/vcredist_x86_all.zip">Microsoft Visual C++ 2017 Redistributable</a></li>
  <li>Install the latest <a href="https://github.com/bwapi/bwapi/releases">BWAPI</a>. At time of writing, this is version 4.4.0</li>
  <li>Install the <a href="https://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html">Java 8 Development Kit</a></li>
  <li>Install the <a href="https://www.jetbrains.com/idea/download/#section=windows">IntelliJ IDEA</a> development environment for Java. This tutorial uses IDEA but other Java development environments work as well.</li>
  <li>Download the <a href="https://github.com/JavaBWAPI/jbwapi-java-template">JBWAPI Java Template</a>. If you have Git you can <code> git clone https://github.com/JavaBWAPI/jbwapi-java-template.git</code>, or you can <a href="https://github.com/JavaBWAPI/jbwapi-java-template/archive/refs/heads/master.zip">download and unzip it</a>.</li></ul>
    <iframe width="1229" height="480" src="https://www.youtube.com/embed/9TT16qUKpR8" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><p><i>Video tutorial for some of these installation steps</i></p>
  
  <h3>Creating your bot</h3>
  <p>Run IntelliJ IDEA. Click "Open" and select the directory where you copied the JBWAPI Java Template. In the file explorer on the left, open <code>jbwapi-java-template &#8594; src &#8594; main &#8594; java &#8594; Bot</code>. This is your new empty bot file.</p>
  <h4>Running your bot</h4>
  <p>Right click <code>main</code> in the code editor and select "Run".</p>
  <img style="float: unset;" src="./images/tutorial-jbwapi-1.png" alt="Running a bot in IDEA by right clicking 'main' and selecting 'Run'" />
  <p>IntelliJ IDEA will compile and run your bot. In a few seconds you should see your Bot's console output saying "Game table mapping not found." That means your bot is successfully running! It's just waiting for you to launch StarCraft so it can start playing.</p>
  <img style="float: unset;" src="./images/tutorial-jbwapi-2.png" alt="The console shows that your bot is running" />  
  <p>Chaoslauncher is the program you'll use to launch StarCraft with BWAPI enabled. It's in the directory where you installed BWAPI under <code>Chaoslauncher\Chaoslauncher.exe</code>. Run it. Go to the Settings tab and disable 'Warn about missing admin privileges'. In the Plugins tab, enable "BWAPI 4.4.0 Injector [RELEASE]". If you'd like to run StarCraft in a window, enable W-MODE.</p>
  <p>Click "Start" to run StarCraft. In StarCraft, click "Single Player" &#8594; "Expansion" &#8594; Create an ID if you don't have one &#8594; Ok &#8594; "Play Custom" &#8594; Select a map you unzipped into "sscai" earlier. Add a Computer opponent and click "Ok" to start the game!</p>
  <img style="float: unset;" src="./images/tutorial-jbwapi-3.png" alt="A screenshot of a StarCraft game showing 'Hello World' text" />
  <p>If your bot connects to the game, its console output will say "Connection successful", and StarCraft should look like the screenshot above. The bot will print "Hello World!" to the screen. You can exit the game now. Everything's set up and you're ready to get coding!</p>
  <p>The next time you run the game from Chaoslauncher, consider configuring BWAPI to start the game automatically. In Chaoslauncher, click the "BWAPI 4.4 Injector [RELEASE]" plugin and the "Config" button to open a text editor containing <code>BWAPI.ini</code>. Edit it to set the following values:</p>
	<ul><li><code>auto_menu = SINGLE_PLAYER</code></li>
	<li><code>maps = maps\sscai\*.sc?</code></li>
	<li>Set <code>race</code> and <code>enemy_race</code> to whatever race you want your bot and enemy to be.</li></ul>
  
	<h3>Event Listeners and the API</h3>
  <p>Throughout this tutorial we will link to the <a href="https://bwapi.github.io/index.html">C++ API documentation</a> because it is most descriptive, but the classes and methods are nearly identical in Java.</p>
	<ul><li>In the source code, you'll find the <code>onFrame()</code> method. That's the implementation of the event listener that's called once on every logical game frame (that's 23.81 times per second on the Fastest game speed setting which most humans use).<br />
There is also the <code>onStart()</code> listener implementation that's called only once, when the game starts.<br />
Most of the simple bots only use these two listeners.
</li>
	<li>However, your bot's code can also be hooked to other <a href="https://javabwapi.github.io/JBWAPI/bwapi/BWEventListener.html">event listeners</a> and get executed after various other game events.
To do that, you can either implement the <a href="https://javabwapi.github.io/JBWAPI/bwapi/BWEventListener.html">interface</a> directly, or extend the stub class <a href="https://javabwapi.github.io/JBWAPI/bwapi/DefaultBWListener.html">DefaultBWListener</a>.</li>
  <li>The API offers other event handlers, described more in the <a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_a_i_module.html#a04a0aed9a49021541a670caace31c3a1">C++ API reference</a>. Some commonly used ones include <code>onUnitCreate</code>, <code>onUnitShow</code>, <code>onUnitMorph</code>, and <code>onUnitDestroy</code>. There's also <a href="https://docs.google.com/document/d/1p7Rw4v56blhf5bzhSnFVfgrKviyrapDFHh9J4FNUXM0/edit">a reference</a> for which unit transitions trigger which events.</li></ul>
 <!---impie66
NOTE: Different units/races in the game may set of different EventListeners. Such as vespene gas collection buildings on construction (Refinery, Extractor, or the assimilator. In these cases they trigger "onUnitMorph". General role of thumb is if something is spawned in it triggers onUnitCreate, if a unit turns into another unit it triggers OnUnitMorph (Zerg use onUnitMorph alot!).</p> -->
 <!-- May be helpful to some, onUnitMorph fucked me over so god dam hard. -->
 <!---impie66 --->
<!--Dan: Good idea. I edited your note and included a link to the transitions reference -->  
  
	<h3>Implementation and Important Classes</h3>
  
  <h4 id="game">The <a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_game.html">Game</a> class</h4>
	<p>The Game object, which you aquire bycalling <code>bwClient.getGame()</code>, gives you access to units, players, and other information about the current game. <code>self()</code> returns your Player object (described below) and <code>enemies()</code> returns the Player objects of your opponents. <code>getAllUnits()</code> returns all units visible to you.</p>
  <p>The Game also provides you with the ability to print draw text or simple geometry overlaid on StarCraft for debugging your bot. See the collection of methods: <code>draw[Line, Circle, Text, etc.]Screen</code> renders shapes using screen overlay coordinates and <code>draw[...]Map</code> renders on top of the game world, subject to camera position.</p>
  <p>StarCraft like most software uses screen coordinates, rather than Cartesian coordinates. The top left corner of the map is (0, 0). Like Cartesian coordinates, X values increase moving right, but Y coordinates increase downwards rather than upwards. This coordinate system also means that angles, as returned by <code>unit.getAngle()</code>, or as used by any trigonometric functions, increase clockwise rather than counter-clockwise.</p>
  <p>Game offers some other tools that can assist your debugging:</p>
    <ul><li><code>game.enableFlag(1)</code> allows you to issue commands from the UI. Your bot is free to override these but this can allow you to manually train units, cancel buildings, or control units that your bot ignores.</li>
    <li><code>game.setLocalSpeed(0)</code> sets the delay between game frames to zero, causing StarCraft to run as fast as possible. In a multiplayer game this is still limited by the speed of your opponents' instances of StarCraft. The argument is a delay, in milliseconds, between game frames. The delay on the "Fastest" speed, used by most humans, is 42. You can also set this delay from the in-game chat by typing "/speed 0"</li></ul>
  
	<h4 id="player">The <a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_player_interface.html">Player</a> class</h4>
	<p>A Player object gives you access to your units, upgrades, and resources. The following lines of code draw some information about the Player on the screen:</p>
  <pre class="prettyprint">Player self = game.self();
game.drawTextScreen(10, 10, "Playing as " + self.getName() + " - " + self.getRace());
game.drawTextScreen(10, 230, "Resources: " + self.minerals() + " minerals,  " + self.gas() + " gas");</pre>
 
 <h4 id="unit">The <a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_unit_interface.html">Unit</a> class</h4>
 <p>Most in-game objects are Units. This includes troops like Zerglings and Overlords, buildings like Gateways, add-ons like Machine Shops, and mineral patches.<br />
   <code>Game.getAllUnits()</code> returns all units. <code>Game.self().getUnits()</code> returns only your units. The Unit interface provides information about units, like <code><a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_unit_interface.html#a524bcbd3dfb6fb82585a49262ec41d23">getType()</a></code> or <code><a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_unit_interface.html#a52d2c6b454a0075656d01e3f46bc970e">getPosition()</a></code>.</p>
	<p>BWAPI only reveals information that would be accessible to a human player. <code>someEnemyPlayer.getUnits()</code> will only return units that are presently visible to your bot. If you are holding on to Unit objects for enemy units that were previously visible but are now hidden, calling methods requesting information on them will return empty values. <code>unit.exists()</code> will let you know if a unit object will return valid data.</p><p>Enemy units which are cloaked but not in range of friendly detectors are available, but only their owner, type, and position will be accessible. If you want your bot to "remember" information about enemy units that may become hidden later, you may want to record and store information about those enemy units in your own objects.</p>
  <p>The Unit object also allows you to issue commands to units:
  <ul><li><code>move(...)</code>: Moves the unit, if it can move</li>
  <li><code>build(...)</code>: If your unit is a worker, instructs it to construct a building</li>
  <li><code>gather(...)</code>: If your unit is a worker, gathers resources (minerals or gas).</li>
  <li><code>attack(...)</code>: Makes your unit attack an enemy unit, or travel towards a position attacking enemies along the way.</li>
  <li><code>train(...)</code>: Trains a new unit, if you have adequate resources to do so.</li>
  <li><code>upgrade(...)</code>: Starts the upgrade in our building (e.g. damage or armor).</li>
  <li><code>research(...)</code>: Starts researching a specified technology/ability in our building (e.g. Stimpacks or Parasite).</li>
  <li><code>rightClick(...)</code>: Does the same thing as if you right-clicked on something in the game with this unit selected.</li></ul>  
  <p>Because units have different purposes, you likely want to issue different kinds of units different commands. This brings us to...</p>
  
  <h4 id="unitType">The <a href="https://bwapi.github.io/class_b_w_a_p_i_1_1_unit_type.html">UnitType</a> class</h4>
  <p>Calling <code>getType()</code> on a unit returns its UnitType which gives you a lot of additional information about the unit, such as its max health, cost, weapon type, or even build time. JBWAPI defines constants for all unit types, you can find them as static class fields in the UnitType class like <code>UnitType.Terran_SCV</code>.
To test, whether a unit is of a particular type, you can compare its type with one of the predefined constants:</p>
<pre class="prettyprint">if (myUnit.getType() == UnitType.Terran_Command_Center && self.minerals() >= 50) {
	myUnit.train(UnitType.Terran_SCV);
  }</pre>
  <p>With this, you can iterate over all your units and issue different commands to each one. For example, the following code makes all your Command Centers train new workers and all your Marines attack the top-left corner of the map:</p>
  <!--Seperated the code from the p tags -- Impie66 --> 
<pre class="prettyprint">//iterate over my units
for (Unit myUnit : self.getUnits()) {
	//if this is a Command Center, make it train additional worker
	if (myUnit.getType() == UnitType.Terran_Command_Center) {
		myUnit.train(UnitType.Terran_SCV);
	}

	//if this is a Marine, let it attack some position
	if (myUnit.getType() == UnitType.Terran_Marine) {
		myUnit.attack(new Position(0, 0));
	}
}</pre>
  <p>Some of these commands (such as attacking or constructing new buildings) require you to specify a location as an argument. Let's take a closer look at position types.</p>
  
  <h4 id="position">Position classes</h4>
  <p>BWAPI, JBWAPI and StarCraft uses three position concepts:</p>
  <ul><li><code>Position</code>: Represents a pixel-precise location. Use <code>unit.getPosition()</code> to get the center position of a unit.</li>
  <li><code>WalkPosition</code>: The resolution of StarCraft's terrain collision grid is 8 pixels wide. A WalkPosition is a coordinate representing an 8-by-8 area.
  <li><code>TilePosition</code>: The resolution of StarCraft's legal building locations, visibility, and cloaked detection is 32 pixels wide. A TilePosition is a coordinate representing a 32-by-32 area. <code>unit.getTilePosition()</code> returns the tile position containing a unit's <i>top-left corner</i>.</li></ul>
  <p>The following code draws the TilePosition and Position of each of your workers right right next to them. The first two arguments specify where on the map to draw this text, with Position-(pixel-)precision (Position.getX() and Position.getY()). Try experimenting with this code yourself.</p>
  <pre class="prettyprint">//iterate over your units
for (Unit myUnit : self.getUnits()) {

	//print TilePosition and Position of my SCVs
	if (myUnit.getType() == UnitType.Terran_SCV) {
		game.drawTextMap(myUnit.getPosition().getX(), myUnit.getPosition().getY(),
			"TilePos: "+myUnit.getTilePosition().toString()+" Pos: "+myUnit.getPosition().toString());
	}
}</pre>
  <p>In a similar manner, you could to print out the current order each unit is performing or even draw a line to their destinations:</p>
<pre class="prettyprint">game.drawTextMap(myUnit.getPosition(), myUnit.getOrder().toString());
game.drawLineMap(myUnit.getPosition(), myUnit.getOrderTargetPosition(),  bwapi.Color.Black);</pre>
  
  <h3 id="map">Constructing Buildings</h3>
  <p>The Game class exposes information about the map's dimensions and which tiles are explored, visible, walkable or buildable. You can use this information to deicde where to place buildings For example, let's take a look at
how you can order your workers to build a Supply Depot:</p>
  <pre class="prettyprint">//if we're running out of supply and have enough minerals...
if (self.supplyTotal() - self.supplyUsed() < 8 && self.minerals() >= 100) {
	//iterate over units to find a worker
	for (Unit myUnit : self.getUnits()) {
		if (myUnit.getType() == UnitType.Terran_SCV) {
			//get a nice place to build a supply depot
			TilePosition buildTile =
				getBuildTile(myUnit, UnitType.Terran_Supply_Depot, self.getStartLocation());
			//and, if found, send the worker to build it (and leave others alone - break;)
			if (buildTile != null) {
				myUnit.build(UnitType.Terran_Supply_Depot, buildTile);
			}
			break;
		}
	}
} </pre>
  <p>This code checks if we're running out of free supply and have enough minerals for the depot. It finds a worker, then finds a TilePosition near our start location where we can build a Supply Depot and orders the worker to do it. <code>getBuildTile()</code> isn't a function in JBWAPI; here's one way you could implement it:</p>
<pre class="prettyprint">// Returns a suitable TilePosition to build a given building type near
// specified TilePosition aroundTile, or null if not found. (builder parameter is our worker)
public TilePosition getBuildTile(Unit builder, UnitType buildingType, TilePosition aroundTile) {
	TilePosition ret = null;
	int maxDist = 3;
	int stopDist = 40;

	// Refinery, Assimilator, Extractor
	if (buildingType.isRefinery()) {
		for (Unit n : game.neutral().getUnits()) {
			if ((n.getType() == UnitType.Resource_Vespene_Geyser) &&
				( Math.abs(n.getTilePosition().getX() - aroundTile.getX()) < stopDist ) &&
				( Math.abs(n.getTilePosition().getY() - aroundTile.getY()) < stopDist )) {
        return n.getTilePosition();
      }
		}
	}

	while ((maxDist < stopDist) && (ret == null)) {
		for (int i=aroundTile.getX()-maxDist; i<=aroundTile.getX()+maxDist; i++) {
			for (int j=aroundTile.getY()-maxDist; j<=aroundTile.getY()+maxDist; j++) {
				if (game.canBuildHere(new TilePosition(i,j), buildingType, builder, false)) {
					// units that are blocking the tile
					boolean unitsInWay = false;
					for (Unit u : game.getAllUnits()) {
						if (u.getID() == builder.getID()) continue;
						if ((Math.abs(u.getTilePosition().getX()-i) < 4) && (Math.abs(u.getTilePosition().getY()-j) < 4)) unitsInWay = true;
					}
					if (!unitsInWay) {
						return new TilePosition(i, j);
					}
					// creep for Zerg
					if (buildingType.requiresCreep()) {
						boolean creepMissing = false;
						for (int k=i; k<=i+buildingType.tileWidth(); k++) {
							for (int l=j; l<=j+buildingType.tileHeight(); l++) {
								if (!game.hasCreep(k, l)) creepMissing = true;
								break;
							}
						}
						if (creepMissing) continue;
					}
				}
			}
		}
		maxDist += 2;
	}

	if (ret == null) game.printf("Unable to find suitable build position for "+buildingType.toString());
	return ret;
}</pre>
  <p>This function searches an increasingly large area (while loop) around aroundTile for TilePositions where <code>game.canBuildHere()</code> returns True. The search starts on 3-tile area and fails (returns null) if no solution is found within 40-tile area around aroundTile. It also skips those tiles, that are blocked by some units and in case of Zerg buildings, it also verifies the creep coverage. Refineries, Assimilators and Extractors are more simple, special cases, since they can only be built on top on Vespene Geysers.</p>

  <h3>Finding the Enemy Base</h3>
  <p>Most StarCraft games involve building new bases next to clusters of minerals and usually another Vespene Geyser. Most maps provide several ideal locations for this. The Game object exposes the possible starting locations with <code>getStartLocations()</code>. To find other good places to put bases (or where your opponent may put them), you can use a terrain analyzer called <a href="http://bwem.sourceforge.net/">BWEM (Brood War Easy Map)</a>. JBWAPI ships with a Java port of BWEM; there's also a <a href="https://github.com/N00byEdge/BWEM-community">modern C++ version</a>.</p>
  <p>This code loads BWEM, gets BWEM's recommended base locations, and draws them on the map:</p>
	<pre class="prettyprint">private BWClient bwClient;
private Game game;
private BWEM bwem; // Stores BWEM data
void onStart() {
game = bwClient.getGame()
// Load BWEM and analyze the map
bwem = new BWEM(game);
bwem.initialize();
}
void onFrame() {
  for (final Base b : bwem.getMap().getBases()) {
    game.drawMap(
      b.getLocation.toPosition(),
      b.getLocation.toPosition().add(new Position(31, 31)),
      Color.Blue);
  }
}</pre>
  <h3>Remembering Enemy Buildings</h3>
There comes a time in the game when you want to attack your opponent.
With the code above, you should be able to find the enemy by sending some units to all BaseLocations.
When you discover some enemy buildings (when you see them, the
  <code class="language-java">game.enemy().getUnits()</code> function returns non-empty set),
you should <b>remember their location</b>, so that you don't need to
look for them in future. Prepare some kind of register for enemy building positions and always keep it up to date.
For example, you can declare the following HashSet that will hold all the positions where we saw an enemy building:
  <pre class="prettyprint">private HashSet<Position> enemyBuildingMemory = new HashSet();</pre>
  <p>Then, somewhere in the <code class="language-java">onFrame()</code> function, you need to keep that HashSet up to date. Take a look at the following example code. First part of it adds currently visible enemy units to memory HashSet, and the second part removes remembered positions if there are no longer any buildings on them (after they've been destroyed).</p>
<pre class="prettyprint">//always loop over all currently visible enemy units (even though this set is usually empty)
for (Unit u : game.enemy().getUnits()) {
	//if this unit is in fact a building
	if (u.getType().isBuilding()) {
		//check if we have it's position in memory and add it if we don't
		if (!enemyBuildingMemory.contains(u.getPosition())) enemyBuildingMemory.add(u.getPosition());
	}
}

//loop over all the positions that we remember
for (Position p : enemyBuildingMemory) {
	// compute the TilePosition corresponding to our remembered Position p
	TilePosition tileCorrespondingToP = new TilePosition(p.getX()/32 , p.getY()/32);

	//if that tile is currently visible to us...
	if (game.isVisible(tileCorrespondingToP)) {

		//loop over all the visible enemy buildings and find out if at least
		//one of them is still at that remembered position
		boolean buildingStillThere = false;
		for (Unit u : game.enemy().getUnits()) {
			if ((u.getType().isBuilding()) && (u.getPosition().equals(p))) {
				buildingStillThere = true;
				break;
			}
		}

		//if there is no more any building, remove that position from our memory
		if (buildingStillThere == false) {
			enemyBuildingMemory.remove(p);
			break;
		}
	}
}</pre>
  <p>In general, you may want to remember much more information in addition to
building positions. It's all up to you.</p>
  <p>And that's it! You should now be able to order your workers to gather resources, order buildings to train new units, construct more buildings, find and remember enemy buildings and send your units to attack them. This covers the basics and should be enough to win some games. There is much, much more you can do to beat your opponents in StarCraft, but we leave that up to you.</p>
  
  <h3>Submitting your bot</h3>
  <p>To submit your bot to the SSCAI Tournament, go to the
<a href="https://sscaitournament.com/index.php?action=submit">Log In &amp; Submit Bots</a> subpage. You should upload a zip archive containing these 3 things:</p>
  <ol><li><b>README file</b> (TXT or PDF) with the description of your bot.</li>
  <li><b>Compiled bot</b>. Either a <i>.JAR</i> file if it's coded in Java (read the instructions below), or <i>.dll</i> file if you used C++.</li></ol>

	<p>If for some reason you decide to disable your bot once submitted, upload an empty ZIP archive as your bot's binaries.</p>

  <h3 id="faq"> Troubleshooting</h3>
  
  <h4>My bot works fine locally but crashes or timeouts while playing on the SSCAIT stream.</h4>
  <p>You should make sure that the release version of your bot that you upload to SSCAIT doesn't try to print to STDOUT (the "console", often via "sys.out.println()" in Java or "printf" or "std::cout" in C++).</p>
  
  <h4>How can I build a standalone version of my bot as a single runnable jar file?</h4>
	<p style="text-align: left;">In IntelliJ IDEA, go to <i>View -> Tool Windows -> Maven</i>, then right click "package" and then "Run Maven Build". This will generate <code>target/jbwapi-template-1.0-SNAPSHOT-jar-with-dependencies.jar</code>, which is a runnable JAR file. You can verify that this JAR bot runs from the Windows command line (Start &#8594; Run &#8594; type in 'cmd'), using the following command:
	<code>java -jar jbwapi-template-1.0-SNAPSHOT-jar-with-dependencies.jar</code><br />Then you can run StarCraft via Chaoslauncher, as usual, and verify that the bot works.
	<img style="float: unset; margin-top: 15px;" src="./images/tutorial-jbwapi-4.png" alt="Creating a runnable jar with Run Maven Build on 'package'" />
</p>

	<h4>My bot can't connect to the game.</h4>
	<p>On some systems, the administrator privileges might be needed for the bot to run. Try running the bot (Eclipse) or Chaoslauncher (or both) as Administrator.</p>

  <h4>Still stuck?</h4>
	<p>The best place to ask questions is the <a href="https://discordapp.com/invite/w9wRRrF">SSCAIT Discord chat room</a>. You can also ask in questions in the <a href="https://www.facebook.com/groups/bwapi/10154329162975261/">BWAPI Facebook group</a>.</p>

<script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?lang=css&skin=sunburst"></script>
