<?php
include("../include.php");

if (!isset($_GET["id"])) $_GET["id"] = "a";
	
drawTop();



$r = db_grab("SELECT tt.name, t.typeID, t.tag FROM intranet_tags t INNER JOIN intranet_tags_types tt ON t.typeID = tt.id WHERE t.id = " . $_GET["id"]);

?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow($r["tag"], 4)?>
	<tr>
		<th width="16"></th>
		<th width="27%" align="left">Name</th>
		<th width="48%" align="left">Company</th>
		<th width="25%" align="left">Phone</th>
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
					JOIN intranet_instances i ON o.instanceCurrentID = i.id
					JOIN intranet_instances_to_tags i2t ON i.id = i2t.instanceID
					WHERE o.isActive = 1 AND i2t.tagID = {$_GET["id"]}
					ORDER BY i.varchar_02, i.varchar_01");
	while ($c = db_fetch($contacts)) {
		if (strlen($c["organization"]) > 40) $c["organization"] = substr($c["organization"], 0, 39) . "...";
		?>
	<tr <?php if (!$c["isActive"]) {?> class="deleted"<?php }?>>
		<td><input type="checkbox"></td>
		<td><a href="contact.php?id=<?php echo $c["id"]?>"><?php echo $c["lastname"]?>, <?php echo $c["firstname"]?></a></td>
		<td><?php echo $c["organization"]?></td>
		<td><?php echo $c["phone"]?><!--<br><?php echo $c["email"]?>--></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>