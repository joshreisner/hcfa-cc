<?php include("../include.php");

drawTop();
drawNavigation();

$result = db_query("SELECT
						u.userID,
						u.lastname last,
						ISNULL(u.nickname, u.firstname) first,
						u.title,
						u.homephone, 
						u.homecell 
					FROM intranet_users u
					WHERE u.rankid < 8 AND u.isactive = 1
					ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
?>
<table class="left">
	<?php echo drawHeaderRow("Management Contact Numbers", 4);?>
	<tr bgcolor="#F6F6F6" class="small">
		<th align="left">Name</th>
		<th align="left">Title</th>
		<th align="left">Home #</th>
		<th align="left">Cell #</th>
	</tr>
	<?php while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="view.php?id=<?php echo $r["userID"]?>"><?php echo $r["first"]?> <?php echo $r["last"]?></a></td>
		<td><?php echo $r["title"]?></td>
		<td width="95"><?php echo format_phone($r["homephone"])?></td>
		<td width="95"><?php echo format_phone($r["homecell"])?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>