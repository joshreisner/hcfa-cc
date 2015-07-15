<?php include("../include.php");

drawTop();

drawNavigation();

$result = db_query("select
						u.userID,
						u.lastname + ', ' + ISNULL(u.nickname, u.firstname) name,
						u.title,
						u.homephone, 
						u.homecell 
					FROM intranet_users u
					WHERE (u.rankid < 8 OR u.departmentID = 7) AND u.isactive = 1
					ORDER BY u.rankID, u.lastname, ISNULL(u.nickname, u.firstname)");
?>

<table class="left">
	<?php echo drawHeaderRow("Management Contact Numbers", 4);?>
	<tr>
		<th align="left">Name</th>
		<th align="left">Title</th>
		<th align="left">Home #</th>
		<th align="left">Cell #</th>
	</tr>
	<?php while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="staff_view.php?id=<?php echo $r["userID"]?>"><?php echo $r["name"]?></a></td>
		<td><?php echo $r["title"]?></td>
		<td><?php echo format_phone($r["homephone"])?></td>
		<td><?php echo format_phone($r["homecell"])?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>