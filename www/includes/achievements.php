
<div style="display: table;">
<div style="display: table-row;">
	<div style="display: table-cell;">
		<h2>Achievements:</h2>
		<table id="achievements_list">
<?php

$res = mysql_query("SELECT title, text, type FROM achievement_texts ORDER BY ordering ASC;");
while ($line = mysql_fetch_assoc($res)) {
	echo '<tr id="'.$line['type'].'"><td><img src="./images/achievements/'.$line['type'].'.png" alt="" /></td><td><h3><a name="'.$line['type'].'">'.$line['title'].'</a></h3>'.$line['text'].'</td></tr>';
}

?>
		</table>
	</div>
	<div style="display: table-cell;">
		<h2 style="margin-left: 10px">Portraits:</h2>
		<table id="portrait_list">
			<tr><td>
			       <img src="./images/portraits/t1.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z1.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p1.gif" class="portrait_big" alt="" />
			</td><td>Unlocked by registration.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t2.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z2.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p2.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 3rd achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t3.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z3.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p3.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 6th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t4.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z4.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p4.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 9th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t5.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z5.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p5.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 12th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t6.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z6.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p6.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 14th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t7.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z7.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p7.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 16th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t8.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z8.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p8.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 18th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t9.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z9.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p9.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked with 20th achievement.</td></tr>
                        <tr><td>
                               <img src="./images/portraits/t10.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/z10.gif" class="portrait_big" alt="" />
                               <img src="./images/portraits/p10.gif" class="portrait_big" alt="" />
                        </td><td>Unlocked when all the achievements have been earned.</td></tr>

		</table>
	</div>
</div>
</div>


