<?php  
include("../include.php");

drawTop();


$r = db_grab("SELECT programDesc FROM intranet_programs WHERE programID = " . $_GET["id"]);
?>

<table class="left" cellspacing="1">
	<?php echo drawHeaderRow($r, 2)?>
	<tr>
		<td class="left">Name</td>
		<td><b><?php echo $r?></b></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?php echo draw_form_button("edit name","program_add_edit.php?id=" . $_GET["id"])?></td>
	</tr>
</table>

<table class="left" cellspacing="1">
	<tr>
		<td colspan="5" class="head">
			Funders Interesed in <?php echo $r?>
		</td>
	</tr>
<?php
	$result = db_query("SELECT 
					f.funderID, 
					f.name 
				FROM resources_funders_program_interests fp
				INNER JOIN resources_funders f ON fp.funderID = f.funderID
				WHERE fp.programID = {$_GET["id"]}
				ORDER BY f.name");
	while ($r = db_fetch($result)) {?>
		<tr>
			<td><a href="funder_view.php?id=<?php echo $r["funderID"]?>"><?php echo $r["name"]?></a></td>
		</tr>
	<?php }?>
</table>

<table class="left" cellspacing="1">
	<tr>
		<td colspan="4" class="head">
			Awards, Proposals, Strategies etc.
		</td>
	</tr>
	<tr>
		<th align="left">Award Name</th>
		<th align="left">Program</th>
		<th align="left">Type</th>
		<th align="right">Amount</th>
	</tr>
<?php
$result_award_statuses = db_query("SELECT 
					s.awardStatusID, 
					s.awardStatusDescPlural,
					(SELECT count(*) FROM resources_awards a WHERE a.awardStatusID = s.awardStatusID AND a.awardProgramID = " . $_GET["id"] . ") as awardCount
				FROM resources_awards_statuses s");
while ($rsa = db_fetch($result_award_statuses)) {
	if (!$rsa["awardCount"]) continue;
	?>
	<tr class="group">
		<td colspan="4"><?php echo $rsa["awardStatusDescPlural"]?></td>
	</tr>
<?php
	$totalAwards = 0;
	$result_awards = db_query("SELECT 
			a.awardID,
			a.awardAmount,
			at.awardTypeDesc,
			a.awardTitle,
			p.programDesc
		FROM resources_awards a
		INNER JOIN resources_awards_types at ON a.awardTypeID = at.awardTypeID
		INNER JOIN intranet_programs p on a.awardProgramID = p.programID
		WHERE a.awardProgramID = " . $_GET["id"] . " 
		AND a.awardStatusID = " . $rsa["awardStatusID"] . "
		ORDER BY a.awardStartDate DESC");
	while ($ra = db_fetch($result_awards)) {
		$totalAwards += $ra["awardAmount"];
	?>
	<tr>
		<td width="99%"><a href="award_view.php?id=<?php echo $ra["awardID"]?>"><?php echo $ra["awardTitle"]?></a></td>
		<td><nobr><?php echo $ra["programDesc"]?></nobr></td>
		<td><nobr><?php echo $ra["awardTypeDesc"]?></nobr></td>
		<td align="right">$<?php echo number_format($ra["awardAmount"])?></td>
	</tr>
	<?php }?>
	<tr class="total">
		<td colspan="3" align="right" width="99%">Total:&nbsp;</td>
		<td>$<?php echo number_format($totalAwards)?></td>
	</tr>
<?php }?>
</table>

<?php drawBottom();?>