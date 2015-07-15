<?php include("../include.php");

drawtop();?>

<table class="left">
	<?php echo drawHeaderRow("Long Distance Codes", 2)?>
	<?php
	$staff = db_query("SELECT userID, firstname, lastname, longDistanceCode FROM intranet_users WHERE isActive = 1 and officeID = 1 ORDER BY lastname, firstname");
	while ($s = db_fetch($staff)) {?>
	<tr>
		<td><a href="/staff/view.php?id=<?php echo $s["userID"]?>"><?php echo $s["lastname"]?>, <?php echo $s["firstname"]?></a></td>
		<td><?php echo sprintf("%04s", $s["longDistanceCode"]);?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>