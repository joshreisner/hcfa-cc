<?php include("include.php");

drawTop();

echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("All Active Users", 3);?>
	<tr>
		<th align="left">Name</td>
		<th align="right" width="50">#</td>
		<th align="right" width="50">%</td>
	</tr>
	<?php
	$result = db_query("SELECT
							u.userID,
							ISNULL(u.nickname, u.firstname) first,
							u.lastname last,
							(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.createdBy = u.userID $where) tickets,
							(SELECT SUM(timeSpent) FROM helpdesk_tickets t WHERE t.createdBy = u.userID " . $where . ") minutes
						FROM intranet_users u
						WHERE u.isActive = 1
						ORDER BY last, first");
	$counter = 0;
	while ($r = db_fetch($result)) {
		if (!$r["tickets"] && $filtered) continue;
		$counter++;
	?>
	<tr>
		<td><a href="user.php?id=<?php echo $r["userID"]?><?php if ($filtered) {?>&month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?><?php }?>"><?php echo $r["first"]?> <?php echo $r["last"]?></a></td>
		<td align="right"><?php echo number_format($r["tickets"])?></td>
		<td align="right"><?php echo @round($r["minutes"] / $total["minutes"] * 100)?></td>
	</tr>
	<?php }
	if (!$counter) {
		if ($filtered) {
			echo drawEmptyResult("No tickets in this month / year.", 3);
		} else {
			echo drawEmptyResult("No tickets.", 3);
		}
	}
	?>
</table>
<?php drawBottom(); ?>