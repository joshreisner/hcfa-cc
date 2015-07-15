<?php include("include.php");

//adding a new type
if ($posting) {
	$_POST["departmentID"] = $departmentID;
	$id = db_enter("helpdesk_tickets_types", "description departmentID", "id");
	url_change();
}

drawTop();

echo drawTicketFilter();
?>

<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Ticket Types", 3, "new type", "#bottom", "excel report", "types-report.php");?>
	<tr>
		<th>Ticket Type</th>
		<th class="r" width="50">#</th>
		<th class="r" width="50">%</th>
	</tr>
	<?php
	$result = db_query("SELECT 
							y.id, 
							y.description,
							(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.typeID = y.id " . $where . ") tickets,
							(SELECT SUM(timeSpent) FROM helpdesk_tickets t WHERE t.typeID = y.id " . $where . ") minutes
						FROM helpdesk_tickets_types y
						WHERE y.departmentID = $departmentID
						ORDER BY y.description");
	$counter = 0;
	while ($r = db_fetch($result)) {
		if (!$r["tickets"] && $filtered) continue;
		$counter++;
		?>
	<tr>
		<td><a href="type.php?id=<?php echo $r["id"]?><?php if ($filtered) {?>&month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?><?php }?>"><?php echo $r["description"]?></a></td>
		<td align="right"><?php echo number_format($r["tickets"])?></td>
		<td align="right"><?php echo @round($r["minutes"] / $total["minutes"] * 100)?></td>
	</tr>
	<?php }
	$t = db_grab("SELECT COUNT(*) tickets, SUM(t.timeSpent) minutes FROM helpdesk_tickets t WHERE t.typeID IS NULL" . $where);
	if ($t["tickets"]) {
		$counter++;
	?>
	<tr>
		<td><a href="type.php<?php if ($filtered) {?>?month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?><?php }?>"><i>No Type</i></a></td>
		<td align="right"><?php echo number_format($t["tickets"])?></td>
		<td align="right"><?php echo @round($t["minutes"] / $total["minutes"] * 100)?></td>
	</tr>
	<?php }
	if (!$counter) {
		if ($filtered) {
			echo drawEmptyResult("No tickets for this month and year.", 3);
		} else {
			echo drawEmptyResult("No tickets.", 3);
		}
	}?>
</table>
<a name="bottom"></a>
<?php 
$form = new intranet_form;
$form->addRow("itext", "Name" , "description", @$r["description"]);
$form->addRow("submit"  , "Add Type");
$form->draw("Add New Type");

drawBottom(); ?>