<?php
include("../include.php");

if (isset($_GET["deleteID"])) {
	$r = db_grab("SELECT endDate FROM intranet_users WHERE userID = " . $_GET["deleteID"]);
	if ($r["endDate"]) {
		db_query("UPDATE intranet_users SET isActive = 0, deletedBy = {$user["id"]}, deletedOn = GETDATE() WHERE userID = " . $_GET["deleteID"]);
	} else {
		db_query("UPDATE intranet_users SET isActive = 0, deletedBy = {$user["id"]}, deletedOn = GETDATE(), endDate = GETDATE() WHERE userID = " . $_GET["deleteID"]);
	}
	url_query_drop("deleteID");
}

drawTop();
drawNavigation();

?>
<table class="left" cellspacing="1">
	<?php if ($isAdmin) {
		echo drawHeaderRow("Consultants", 6, "add new staff member", "add_edit.php");
	} else {
		echo drawHeaderRow("Consultants", 5);
	} ?>
	<tr>
		<th></th>
		<th align="left">Name</th>
		<th align="left">Title</th>
		<th align="left">Location</th>
		<th align="left">Phone</th>
	<?php if ($isAdmin) {?>
		<th></th>
	<?php } ?>
	</tr>
	<?php
		
	$result = db_query("SELECT 
							u.userID, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone, 
							f.name office, 
							u.title, 
							d.departmentName,
							u.imageID,
							m.height,
							m.width,
							r.isPayroll
						FROM intranet_users u
						JOIN intranet_ranks r ON u.rankID = r.id
						LEFT  JOIN intranet_departments d ON d.departmentID = u.departmentID 
						LEFT  JOIN intranet_offices f     ON f.id = u.officeID
						LEFT  JOIN intranet_images m      ON u.imageID = m.imageID
						WHERE u.isActive = 1 AND r.isPayroll = 0
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	while ($r = db_fetch($result)) {?>
	<tr height="38">
		<?php if ($r["imageID"]) {
			verifyImage($r["imageID"]);
			$factor      = (31 / $r["height"]);
			$r["width"]  = $r["width"]  * $factor;
			$r["height"] = $r["height"] * $factor;
			?>
		<td width="47" align="center"><a href="/staff/view.php?id=<?php echo $r["userID"]?>"><img src="/data/staff/<?php echo $r["imageID"]?>.jpg" width="<?php echo $r["width"]?>" height="<?php echo $r["height"]?>" border="0"></a></td>
		<?php } else {?>
		<td>&nbsp;</td>
		<?php }?>
		<td><nobr><a href="view.php?id=<?php echo $r["userID"]?>"><?php echo $r["lastname"]?>, <?php echo $r["firstname"]?></a></nobr></td>
		<td><?php echo $r["title"]?></td>
		<td><?php echo $r["office"]?></td>
		<td align="right"><nobr><?php echo format_phone($r["phone"])?></nobr></td>
		<?php echo deleteColumn("Delete this staff member?", $r["userID"])?>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>