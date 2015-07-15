<?php include("../include.php");
drawTop();
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Organizations", 4, "new", "organization_add_edit.php");?>
	<tr>
		<th align="left">Organization</th>
		<th align="left" style="width:120px">City, State</th>
		<th align="right" style="width:100px">Last Update</th>
	</tr>
	<?php
	$result = db_query("SELECT 
							o.id,
							o.name, 
							o.phone,
							z.city, 
							z.state, 
							o.zip,
							o.lastUpdatedOn
						FROM web_organizations o
						INNER JOIN zip_codes z ON o.zip = z.zip
						ORDER BY name");
	while ($r = db_fetch($result)) {?>
		<tr>
			<td><a href="organization_view.php?id=<?php echo $r["id"]?>"><?php echo $r["name"]?></a></td>
			<td><?php echo $r["city"]?>, <?php echo $r["state"]?></td>
			<td align="right"><?php echo format_date($r["lastUpdatedOn"])?></td>
		</tr>
	<?php }?>
</table>
<?php drawBottom();?>