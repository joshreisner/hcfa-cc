<?php
include("include.php");

drawTop();
echo drawJumpToStaff();

?>

<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Emergency Contact Information", 3);?>
	<tr>
		<th align="left" width="30%">Name</th>
		<th align="left" width="35%">Personal Info</th>
		<th align="left" width="35%">Emergency Contacts</th>
	</tr>
	<?php
	$result = db_query("SELECT 
							u.userID,
							u.firstname first_name,
							u.lastname last_name,
							u.homeAddress1,
							u.homeAddress2,
							u.homeCity,
							s.stateAbbrev as homeState,
							u.homeZIP,
							u.homePhone,
							u.homeCell,
							u.homeEmail,
							u.emerCont1Name,
							u.emerCont1Relationship,
							u.emerCont1Phone,
							u.emerCont1Cell,
							u.emerCont1Email,
							u.emerCont2Name,
							u.emerCont2Relationship,
							u.emerCont2Phone,
							u.emerCont2Cell,
							u.emerCont2Email
						FROM intranet_users u
						LEFT  JOIN intranet_us_states s ON s.stateID = u.homeStateID
						WHERE u.isActive = 1
						ORDER BY u.lastname, u.firstname");
	while ($r = db_fetch($result)) {?>
		<tr>
			<td rowspan="2"><a href="/staff/view.php?id=<?php echo $r["userID"]?>"><?php echo $r["last_name"]?>, <?php echo $r["first_name"]?></a></td>
			<td rowspan="2">
				<?php echo $r["homeAddress1"]?><br>
				<?php if ($r["homeAddress2"]) {?><?php echo $r["homeAddress2"]?><br><?php }?>
				<?php if ($r["homeCity"]) {?><?php echo $r["homeCity"]?>, <?php echo $r["homeState"]?> <?php echo $r["homeZIP"]?><br><?php }?>
				<?php if ($r["homePhone"]) {?><?php echo $r["homePhone"]?> (Home)<br><?php }?>
				<?php if ($r["homeCell"]) {?><?php echo $r["homeCell"]?> (Cell)<br><?php }?>
				<a href="mailto:<?php echo $r["homeEmail"]?>"><?php echo $r["homeEmail"]?></a>
			</td>
			<td>
				<?php if ($r["emerCont1Name"]) {?><?php echo $r["emerCont1Name"]?> (<?php echo $r["emerCont1Relationship"]?>)<br><?php }?>
				<?php if ($r["emerCont1Phone"]) {?><?php echo $r["emerCont1Phone"]?><?php }?> 
				<?php if ($r["emerCont1Phone"] && $r["emerCont1Cell"]) {?> / <?php }?>
				<?php if ($r["emerCont1Cell"]) {?><?php echo $r["emerCont1Cell"]?><?php }?>
				<?php if ($r["emerCont1Phone"] || $r["emerCont1Cell"]) {?><br><?php }?>
				<a href="mailto:<?php echo $r["emerCont1Email"]?>"><?php echo $r["emerCont1Email"]?></a>
			</td>
		</tr>
		<tr>
			<td>
				<?php if ($r["emerCont2Name"]) {?><?php echo $r["emerCont2Name"]?> (<?php echo $r["emerCont2Relationship"]?>)<br><?php }?>
				<?php if ($r["emerCont2Phone"]) {?><?php echo $r["emerCont2Phone"]?><?php }?> 
				<?php if ($r["emerCont2Phone"] && $r["emerCont2Cell"]) {?> / <?php }?>
				<?php if ($r["emerCont2Cell"]) {?><?php echo $r["emerCont2Cell"]?><?php }?>
				<?php if ($r["emerCont2Phone"] || $r["emerCont2Cell"]) {?><br><?php }?>
				<a href="mailto:<?php echo $r["emerCont2Email"]?>"><?php echo $r["emerCont2Email"]?></a>
			</td>
		</tr>
	<?php }?>
</table>
<?php drawBottom();?>