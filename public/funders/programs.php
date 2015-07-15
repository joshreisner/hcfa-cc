<?php  
include("../include.php");

if (url_action("delete")) {
	db_query("DELETE FROM intranet_programs WHERE programID = " . $_GET["id"]);
	url_drop();
}
drawTop();
?>

<table class="left" cellspacing="1">
<?php if ($isAdmin) {
	echo drawHeaderRow("Programs", 4, "new program", "program_add_edit.php");
} else {
	echo drawHeaderRow("Programs", 4);
} ?>
	<tr>
		<th width="70%" align="left">Program Name</th>
		<th width="15%" align="right"># awards</th>
		<th width="15%" align="right"># funders</th>
		<th width="16"></th>
	</tr>
<?php
$result = db_query("SELECT p.programID, p.programDesc,
			(SELECT count(*) FROM resources_awards a WHERE a.awardProgramID = p.programID) as awardCount,
			(SELECT count(*) FROM resources_funders_program_interests f WHERE f.programID = p.programID) as funderCount
			FROM intranet_programs p ORDER BY programDesc");
while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="program.php?id=<?php echo $r["programID"]?>"><?php echo $r["programDesc"]?></td>
		<td align="right"><?php echo $r["awardCount"]?></td>
		<td align="right"><?php echo $r["funderCount"]?></td>
		<?php if (($r["awardCount"] == 0) && ($r["funderCount"] == 0)) {
			echo deleteColumn("Delete this program?", $r["programID"]);
		} else {?>
			<td width="16"><img src="<?php echo $locale?>images/icons/delete-disabled.gif" width="16" height="16" border="0"></td>
		<?php }?>
	</tr>
<?php }?>
</table>
<?php drawBottom();?>