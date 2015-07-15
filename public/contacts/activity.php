<?php include("../include.php");
drawTop();
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Recent Activity", 4);?>
	<tr>
		<th width="40%" align="left">Contact Record</th>
		<th width="20%" align="left">Action</th>
		<th width="20%">Done By</th>
		<th width="20%" align="right">When</th>
	</tr>
	<?php
	$result = db_query("SELECT
			o.id,
			o.isActive,
			i.varchar_02,
			i.varchar_01,
			i.createdOn,
			(SELECT COUNT(*) FROM intranet_instances i2 WHERE i2.objectID = o.id) occurrences,
			i.createdBy,
			ISNULL(u.nickname, u.firstname) updatename,
			o.isactive
		FROM intranet_objects o
		INNER JOIN intranet_instances	i ON o.instanceCurrentID = i.id
		INNER JOIN intranet_users		u ON i.createdBy = u.userID
		ORDER BY i.createdOn DESC", 40);
	while ($r = db_fetch($result)) {?>
	<tr class="<?php if(!$r["isActive"]){?>-deleted<?php }?>">
		<td><a href="contact.php?id=<?php echo $r["id"]?>"><?php echo $r["varchar_02"]?>, <?php echo $r["varchar_01"]?></a></td>
		<td><?php if ($r["occurrences"] == 1) {?>New Contact<?php } else {?>Update<?php }?></td>
		<td align="center"><a href="/staff/view.php?id=<?php echo $r["createdBy"]?>"><?php echo $r["updatename"]?></a></td>
		<td align="right"><?php echo format_date($r["createdOn"])?></td>
	</tr>
	<?php } ?>
</table>
<?php drawBottom();?>