<?php
include("../include.php");

drawTop();


$result = db_query("SELECT
					u.userID,
					u.firstname,
					u.lastname,
					u.title,
					o.name office
				FROM intranet_users u
				JOIN intranet_offices o on u.officeID = o.officeID
				JOIN intranet_ranks r ON u.rankID = r.id
				WHERE u.imageID is null and u.isactive = 1 and r.ispayroll = 1
				ORDER BY o.name, u.lastname, u.firstname");
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Staff Without Pictures", 3)?>
	<tr>
		<th align="left" width="33%">Name</th>
		<th align="left" width="33%">Title</th>
		<th align="left" width="33%">Office</th>
	</tr>
	<?php while ($r = db_fetch($result)) {?>
	<tr>
		<td width="33%"><a href="/staff/view.php?id=<?php echo $r["userID"]?>"><?php echo $r["lastname"]?>, <?php echo $r["firstname"]?></a></td>
		<td width="33%"><?php echo $r["title"]?></td>
		<td width="33%"><?php echo $r["office"]?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>