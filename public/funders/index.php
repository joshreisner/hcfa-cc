<?php
include("../include.php");

if (isset($_GET["deleteID"])) { //delete a funder
	db_query("DELETE FROM resources_activity                     WHERE funderID = " . $_GET["deleteID"]);
	db_query("DELETE FROM resources_funders_geographic_interests WHERE funderID = " . $_GET["deleteID"]);
	db_query("DELETE FROM resources_funders_program_interests    WHERE funderID = " . $_GET["deleteID"]);
	db_query("DELETE FROM resources_funders                      WHERE funderID = " . $_GET["deleteID"]);
	url_drop();
}

drawTop();
	
?>
<table class="left" cellspacing="1">
<?php
if ($isAdmin && !$printing) {
	$colspan = 6;
	echo drawHeaderRow("Funders", $colspan, "add a funder", "funder_add_edit.php");
} else {
	$colspan = 5;
	echo drawHeaderRow("Funders", $colspan);
}?>
	<tr>
		<th width="60%" align="left">Funder Name</th>
		<th width="39%" align="left">Staff Contact</th>
		<th align="right">Total Awards</th>
		<?php if ($isAdmin && !$printing) {?><th width="16"></th><?php }?>
	</tr>
<?php
$result_funder_statuses = db_query("SELECT funderStatusID, funderStatusDesc FROM resources_funders_statuses");
while ($rs = db_fetch($result_funder_statuses)) {
	$awards_amt  = 0;?>
	<tr class="group">
		<td colspan="<?php echo $colspan?>"><b><?php echo $rs["funderStatusDesc"]?>s</b></td>
	</tr>
	<?php
		$result = db_query("SELECT 
			f.funderID,
			f.name,
			f.staffID,
			ft.funderTypeDesc,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			(SELECT SUM(a.AwardAmount) FROM resources_awards a WHERE a.funderID = f.funderID AND (a.awardStatusID = 1 OR a.awardStatusID = 4)) as awardAmt
			FROM resources_funders f
			INNER JOIN resources_funders_types ft ON f.funderTypeID = ft.funderTypeID
			INNER JOIN intranet_users u     ON f.staffID           = u.userID
			WHERE f.funderStatusID = {$rs["funderStatusID"]}
			ORDER BY f.name");
	 		
		while ($r = db_fetch($result)) {
			$awards_amt  += ($r["awardAmt"]);?>
		<tr>
			<td><a href="funder_view.php?id=<?php echo $r["funderID"]?>"><?php echo $r["name"]?></a></td>
			<td><?php echo $r["first"]?> <?php echo $r["last"]?></td>
			<td align="right">$<?php echo number_Format($r["awardAmt"])?></td>
			<?php echo deleteColumn("Delete this funder?", $r["funderID"]);?>
		</tr>
	<?php }?>
	<tr class="total">
		<td colspan="2">Total: </td>
		<td>$<?php echo number_format($awards_amt)?></td>
		<?php if ($isAdmin && !$printing) {?><td></td><?php }?>
	</tr>
<?php }?>
</table>
<?php drawBottom(); ?>