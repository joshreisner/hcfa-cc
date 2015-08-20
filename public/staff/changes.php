<?php	include("include.php");
drawTop();
echo drawJumpToStaff();
?>
<table class="left" cellspacing="1">
	<?php
	if ($isAdmin) {
		echo drawHeaderRow("Comings", 2, "new", "add_edit.php");
	} else {
		echo drawHeaderRow("Comings", 2);
	}
	$staff = db_query("SELECT
		u.userID,
		ISNULL(u.nickname, u.firstname) first, 
		u.lastname last,
		u.title,
		d.departmentName,
		o.name office,
		u.startdate,
		u.bio
	FROM intranet_users u
	JOIN intranet_offices o ON u.officeID = o.id
	JOIN intranet_departments d ON u.departmentID = d.departmentID
	WHERE " . db_datediff("u.startdate", "GETDATE()") . " < 60 AND u.isActive = 1
	ORDER BY u.startdate DESC");

	while ($s = db_fetch($staff)) {?>
	<tr>
		<td width="129" height="90" align="center" style="padding:1px;"><?php
			echo "<a href='/staff/view.php?id=" . $s["userID"] . "'>" . drawImg($s['userID']) . "</a>";
		?></td>
		<td class="text">
			<b><a href="/staff/view.php?id=<?php echo $s["userID"]?>"><?php echo $s["first"]?> <?php echo $s["last"]?></a></b> &nbsp;<span class="light"><?php echo format_date($s["startdate"])?></span><br>
			<?php echo $s["title"]?><br>
			<?php echo $s["departmentName"]?><br>
			<?php echo $s["office"]?><br>
			<?php echo $s["bio"]?>
		</td>
	</tr>
	<?php }?>
</table>

<?php
$result = db_query("SELECT 
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.title,
			d.departmentName,
			u.userID, 
			u.endDate
			FROM intranet_users u
			JOIN intranet_departments d ON u.departmentID = d.departmentID
			WHERE " . db_datediff("u.endDate", "GETDATE()") . " < 32 ORDER BY endDate DESC");
?>

<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Goings", 4);?>
	<tr>
		<th width="47"></th>
		<th width="25%" align="left">Name</th>
		<th width="50%" align="left">Title, Department</th>
		<th width="20%" align="right">Last Day</th>
	</tr>
	<?php while ($r = db_fetch($result)) {?>
	<tr bgcolor="#FFFFFF" class="helptext" valign="top" height="38">
		<td><?php echo drawImg($r['userID'])?></td>
		<td><a href="/staff/view.php?id=<?php echo $r["userID"]?>"><?php echo $r["first"]?> <?php echo $r["last"]?></a></td>
		<td><?php echo $r["title"]?>, <?php echo $r["departmentName"]?></td>
		<td align="right"><?php echo format_date($r["endDate"]);?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();