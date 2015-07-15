<?	include("include.php");

drawTop();

echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Tickets by Administrator", 3, "by month", "admins-report.php", "by age", "admins-byage.php");?>
	<tr>
		<th>Location</td>
		<th class="r" width="50">#</td>
		<th class="r" width="50">%</td>
	</tr>
	<? 
	$counter = 0;
	$users = db_query("SELECT 
		u.userID,
		ISNULL(u.nickname, u.firstname) first, 
		u.lastname last,
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE u.userID = t.ownerID $where) tickets,
		(SELECT SUM(timeSpent) FROM helpdesk_tickets t WHERE u.userID = t.ownerID $where) minutes		
		FROM intranet_users u
		JOIN administrators a ON u.userID = a.userID
		WHERE a.moduleID = 3 AND u.departmentID = $departmentID
		ORDER BY last, first");
	while ($u = db_fetch($users)) {
		if (!$u["tickets"] && $filtered) continue;
		$counter++;
		?>
		<tr class="helptext" bgcolor="#FFFFFF">
			<td><a href="admin.php?id=<?=$u["userID"]?><? if ($filtered) {?>&month=<?=$_GET["month"]?>&year=<?=$_GET["year"]?><? }?>"><?=$u["last"]?>, <?=$u["first"]?></td>
			<td align="right"><?=number_format($u["tickets"])?></a></td>
			<td align="right"><?=@round($u["minutes"] / $total["minutes"] * 100)?></td>
		</tr>
	<? }
	if (!$counter) {
		if ($filtered) {
			echo drawEmptyResult("No tickets were assigned to this admin in this month / year.", 3);
		} else {
			echo drawEmptyResult("No tickets have been assigned to this admin.", 3);
		}
	}
	?>
</table>
<? drawBottom();?>