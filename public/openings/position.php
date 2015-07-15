<?php
include("../include.php");
drawTop();

$r = db_grab("SELECT 
		j.id,
		j.title,
		j.description,
		c.description corporationName,
		o.name office,
		j.createdOn, 
		j.updatedOn,
		j.deletedOn,
		u1.firstname createdByFirst,
		u1.lastname createdByLast,
		u2.firstname updatedByFirst,
		u2.lastname updatedByLast,
		u3.firstname deletedByFirst,
		u3.lastname updatedByLast
	FROM intranet_jobs j
	LEFT JOIN organizations c ON j.corporationID = c.id
	LEFT JOIN intranet_offices o ON j.officeID = o.id
	LEFT JOIN intranet_users u1 ON j.createdBy = u1.userID
	LEFT JOIN intranet_users u2 ON j.updatedBy = u2.userID
	LEFT JOIN intranet_users u3 ON j.deletedBy = u3.userID
	
	WHERE j.id = " . $_GET["id"]);
	$r["createdBy"] = ($r["createdByFirst"]) ? $r["createdByFirst"] . " " . $r["createdByLast"] : false;
	$r["updatedBy"] = ($r["updatedByFirst"]) ? $r["updatedByFirst"] . " " . $r["updatedByLast"] : false;
	$r["deletedBy"] = ($r["deletedByFirst"]) ? $r["deletedByFirst"] . " " . $r["deletedByLast"] : false;
?>
<table class="left" cellspacing="1">
	<?php if ($isAdmin) {
		echo drawHeaderRow("View Position", 2, "edit", "position_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Position", 2);
	}?>
	<tr>
		<td class="left">Organization</td>
		<td><?php echo $r["corporationName"]?></td>
	</tr>
	<tr>
		<td class="left">Location</td>
		<td><?php echo $r["office"]?></td>
	</tr>
	<tr>
		<td class="left">Position</td>
		<td class="text">
			<h1><?php echo $r["title"]?></h1>
			<?php echo $r["description"]?>
		</td>
	</tr>
	<?php if ($r["createdOn"]) {?>
	<tr>
		<td class="left">Posted</td>
		<td><?php echo format_date($r["createdOn"])?> by <?php echo $r["createdBy"]?></td>
	</tr>
	<?php }
	if ($r["updatedOn"]) {?>
	<tr>
		<td class="left">Updated</td>
		<td><?php echo format_date($r["updatedOn"])?> by <?php echo $r["updatedBy"]?></td>
	</tr>
	<?php }
	if ($r["deletedOn"]) {?>
	<tr>
		<td class="left">Deleted</td>
		<td><?php echo format_date($r["deletedOn"])?> by <?php echo $r["deletedBy"]?></td>
	</tr>
	<?php } ?>
</table>
<?php drawBottom();?>