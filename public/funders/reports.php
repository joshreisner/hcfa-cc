<?php include("../include.php");

drawTop();


?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Reports", 4)?>
	<tr>
		<th align="left" width="40%">Funder / Award</th>
		<th align="right">Start / End Date</th>
		<!--<th align="left" width="20%">Assigned To</th>-->
		<th align="left" width="20%">Activity</th>
		<th align="right">Due Date</th>
	</tr>
	<?php
	//not sure why, but activityAssignedTo is returning empty on first row (sara johnston)
	//commenting out, bc don't think it's worth fixing right now
	$result = db_query("SELECT
	f.funderID,
	f.name,
	a.awardID,
	a.awardTitle,
	a.awardStartDate,
	a.awardEndDate,
	(SELECT TOP 1 c.activityAssignedTo FROM resources_activity c WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 AND isComplete = 0 ORDER BY activityDate ASC) activityAssignedTo,
	(SELECT TOP 1 ISNULL(u.nickname, u.firstname) + ' ' + u.lastname FROM intranet_users u JOIN resources_activity c ON c.activityAssignedTo = u.userID WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 ORDER BY activityDate ASC) activityAssignedName,
	(SELECT TOP 1 c.activityDate       FROM resources_activity c WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 AND isComplete = 0 ORDER BY activityDate ASC) activityDate,
	(SELECT TOP 1 c.activityTitle      FROM resources_activity c WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 AND isComplete = 0 ORDER BY activityDate ASC) activityTitle
		FROM resources_funders f
		JOIN resources_awards a on f.funderID = a.funderID
		WHERE f.fundertypeID <> 7 and awardstatusID = 1
		ORDER BY f.name, a.awardTitle");
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="funder_view.php?id=<?php echo $r["funderID"]?>"><?php echo $r["name"]?></a> / <br>
			<a href="award_view.php?id=<?php echo $r["awardID"]?>"><?php echo $r["awardTitle"]?></a></td>
		<td align="right"><?php echo format_date($r["awardStartDate"]);?><br><?php echo format_date($r["awardEndDate"]);?></td>
		<!--<td><a href="/staff/view.php?id=<?php echo $r["activityAssignedTo"]?>"><?php echo $r["activityAssignedName"]?></a></td>-->
		<td><?php echo $r["activityTitle"]?></td>
		<td align="right"><?php echo format_date($r["activityDate"], false, "", "");?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>