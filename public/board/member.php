<?php
include("../include.php");
drawTop();
	
$r = db_grab("SELECT
		m.firstname,
		m.lastname,
		m.bio,
		m.positionOnBoard,
		m.employment,
		o.description organization
	FROM board_members m
	JOIN organizations o ON m.corporationID = o.id
	WHERE m.id = " . $_GET["id"]);
?>
<table class="left" cellspacing="1">
	<?php if ($isAdmin) {
		echo drawHeaderRow("Board Member", 2, "edit", "member_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("Board Member", 2);
	}?>
	<tr>
		<td class="left">Name</td>
		<td><h1><?php echo $r["firstname"]?> <?php echo $r["lastname"]?></h1></td>
	</tr>
	<tr>
		<td class="left">Organization</td>
		<td><?php echo $r["organization"]?></td>
	</tr>
	<tr>
		<td class="left">Position on Board</td>
		<td><?php echo $r["positionOnBoard"]?></td>
	</tr>
	<tr>
		<td class="left">Employment</td>
		<td><?php echo $r["employment"]?></td>
	</tr>
	<tr>
		<td class="left">Bio</td>
		<td class="text"><?php echo $r["bio"]?></td>
	</tr>
</table>
<?php drawBottom(); ?>