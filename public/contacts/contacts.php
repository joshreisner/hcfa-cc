<?php
include("../include.php");

if (!isset($_GET["id"])) $_GET["id"] = "a";
	
drawTop();



if (!isset($_GET["print"])) {?>
<table class="navigation contacts" cellspacing="1">
	<tr class="contacts-hilite">
		<td width="3.846%"<?php if ($_GET["id"] != "a") {?>><a href="contacts.php?id=a"><?php }else{?> class="selected"><b><?php }?>A</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "b") {?>><a href="contacts.php?id=b"><?php }else{?> class="selected"><b><?php }?>B</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "c") {?>><a href="contacts.php?id=c"><?php }else{?> class="selected"><b><?php }?>C</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "d") {?>><a href="contacts.php?id=d"><?php }else{?> class="selected"><b><?php }?>D</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "e") {?>><a href="contacts.php?id=e"><?php }else{?> class="selected"><b><?php }?>E</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "f") {?>><a href="contacts.php?id=f"><?php }else{?> class="selected"><b><?php }?>F</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "g") {?>><a href="contacts.php?id=g"><?php }else{?> class="selected"><b><?php }?>G</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "h") {?>><a href="contacts.php?id=h"><?php }else{?> class="selected"><b><?php }?>H</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "i") {?>><a href="contacts.php?id=i"><?php }else{?> class="selected"><b><?php }?>I</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "j") {?>><a href="contacts.php?id=j"><?php }else{?> class="selected"><b><?php }?>J</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "k") {?>><a href="contacts.php?id=k"><?php }else{?> class="selected"><b><?php }?>K</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "l") {?>><a href="contacts.php?id=l"><?php }else{?> class="selected"><b><?php }?>L</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "m") {?>><a href="contacts.php?id=m"><?php }else{?> class="selected"><b><?php }?>M</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "n") {?>><a href="contacts.php?id=n"><?php }else{?> class="selected"><b><?php }?>N</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "o") {?>><a href="contacts.php?id=o"><?php }else{?> class="selected"><b><?php }?>O</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "p") {?>><a href="contacts.php?id=p"><?php }else{?> class="selected"><b><?php }?>P</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "q") {?>><a href="contacts.php?id=q"><?php }else{?> class="selected"><b><?php }?>Q</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "r") {?>><a href="contacts.php?id=r"><?php }else{?> class="selected"><b><?php }?>R</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "s") {?>><a href="contacts.php?id=s"><?php }else{?> class="selected"><b><?php }?>S</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "t") {?>><a href="contacts.php?id=t"><?php }else{?> class="selected"><b><?php }?>T</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "u") {?>><a href="contacts.php?id=u"><?php }else{?> class="selected"><b><?php }?>U</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "v") {?>><a href="contacts.php?id=v"><?php }else{?> class="selected"><b><?php }?>V</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "w") {?>><a href="contacts.php?id=w"><?php }else{?> class="selected"><b><?php }?>W</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "x") {?>><a href="contacts.php?id=x"><?php }else{?> class="selected"><b><?php }?>X</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "y") {?>><a href="contacts.php?id=y"><?php }else{?> class="selected"><b><?php }?>Y</a></td>
		<td width="3.846%"<?php if ($_GET["id"] != "z") {?>><a href="contacts.php?id=z"><?php }else{?> class="selected"><b><?php }?>Z</a></td>
	</tr>
</table>
<?php }?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow(strToUpper($_GET["id"]), 4)?>
	<tr>
		<th width="" align="left">Name</th>
		<th width="" align="left">Company</th>
		<th width="" align="left">Phone</th>
	</tr>
	<?php
	$contacts = db_query("SELECT
						o.id,
						o.isActive,
						i.varchar_01 as firstname,
						i.varchar_02 as lastname,
						i.varchar_04 as organization,
						i.varchar_08 as phone,
						i.varchar_11 as email
					FROM intranet_objects o
					INNER JOIN intranet_instances i ON o.instanceCurrentID = i.id
					WHERE o.isActive = 1 AND i.varchar_02 LIKE '" . $_GET["id"] . "%'
					ORDER BY i.varchar_02, i.varchar_01");
	while ($c = db_fetch($contacts)) {
		if (strlen($c["organization"]) > 40) $c["organization"] = substr($c["organization"], 0, 39) . "...";
		?>
	<tr>
		<td><a href="contact.php?id=<?php echo $c["id"]?>"><?php echo $c["lastname"]?>, <?php echo $c["firstname"]?></a></td>
		<td><?php echo $c["organization"]?></td>
		<td><?php echo $c["phone"]?><!--<br><?php echo $c["email"]?>--></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>