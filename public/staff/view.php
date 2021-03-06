<?php
include("include.php");

//delete user handled by include
if (url_action("undelete")) { //undelete user
	db_query("UPDATE intranet_users SET isActive = 1, deletedBy = NULL, deletedOn = NULL, endDate = NULL, updatedBy = {$user["id"]}, updatedOn = GETDATE() WHERE userID = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("passwd")) {
	db_query("UPDATE intranet_users SET password = PWDENCRYPT('') WHERE userID = " . $_GET["id"]);
	$r = db_grab("SELECT userID, email FROM intranet_users WHERE userID = " . $_GET["id"]);
	email_user($r["email"], "Intranet Password Reset", drawEmptyResult($user["first"] . ' has just reset your password on the Intranet.  To pick a new password, please <a href="http://' . $_josh["request"]["host"] . '/login/password_reset.php?id=' . $r["userID"] . '">follow this link</a>.'));
	url_query_drop("action");
} elseif (url_action("invite")) {
	$r = db_grab("SELECT nickname, email, firstname FROM intranet_users WHERE userID = " . $_GET["id"]);
	$name = (!$r["nickname"]) ? $r["firstname"] : $r["nickname"];
	email_invite($_GET["id"], $r["email"], $name);
	url_query_drop("action");
}

url_query_require();

drawTop();

$r = db_grab("SELECT 
		u.firstname,
		u.lastname,
		u.nickname, 
		u.bio, 
		u.email,
		" . db_pwdcompare("", "u.password") . " password,
		u.phone, 
		u.lastlogin, 
		u.title,
		f.name office, 
		d.departmentName,
		u.corporationID,
		c.description corporationName,
		u.homeAddress1,
		u.homeAddress2,
		u.homeCity,
		s.stateAbbrev,
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
		u.emerCont2Email,
		u.startDate,
		u.longDistanceCode,
		u.endDate,
		u.isActive,
		r.description rank
	FROM intranet_users u
	JOIN intranet_ranks r ON u.rankID = r.id
	LEFT  JOIN organizations			c ON u.corporationID = c.id
	LEFT  JOIN intranet_departments		d ON d.departmentID	= u.departmentID 				
	LEFT  JOIN intranet_offices    		f ON f.id			= u.officeID 				
	LEFT  JOIN intranet_us_states		s ON u.homeStateID	= s.stateID
	WHERE u.userID = " . $_GET["id"]);
				
$r["corporationName"] = (empty($r["corporationName"])) ? '<a href="organizations.php?id=0">Shared</a>' : '<a href="organizations.php?id=' . $r["corporationID"] . '">' . $r["corporationName"] . '</a>';

if (!isset($r["isActive"])) url_change("./");

echo drawJumpToStaff($_GET["id"]);

if (!$r["isActive"]) {
	$msg = "This is a former staff member.  ";
	if ($r["endDate"]) {
		$msg .= ($r["nickname"]) ? $r["nickname"] : $r["firstname"];
		$msg .= "'s last day was " . format_date($r["endDate"]) . ".";
	}
	echo drawServerMessage($msg, "center");
}
?>
<table class="left" cellspacing="1">
	<?php
	if ($isAdmin) {
		if ($r["isActive"]) {
			echo drawHeaderRow("View Staff Info", 3, "edit", "add_edit.php?id=" . $_GET["id"], "deactivate", deleteLink("Deactivate this staff member?"));
		} else {
			echo drawHeaderRow("View Staff Info", 3, "edit", "add_edit.php?id=" . $_GET["id"], "re-activate", deleteLink("Re-activate this staff member?", false, "undelete"));
		}
	} elseif ($_GET["id"] == $user["id"]) {
		echo drawHeaderRow("View Staff Info", 3, "edit your info", "add_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Staff Info", 3);
	}
	?>
	<tr>
		<td class="left">Name</td>
		<td width="99%" class="big"><?php echo $r["firstname"]?> <?php if ($r["nickname"]) {?>(<?php echo $r["nickname"]?>) <?php }?><?php echo $r["lastname"]?></td>
		<td rowspan="8" class="profile_image"><?php echo drawImg($_GET['id'])?></td>
	</tr>
	<tr>
		<td class="left">Organization</td>
		<td><?php echo $r["corporationName"]?></td>
	</tr>
	<tr>
		<td class="left">Title</td>
		<td><?php echo $r["title"]?></td>
	</tr>
	<tr>
		<td class="left">Department</td>
		<td><?php echo $r["departmentName"]?></td>
	</tr>
	<tr>
		<td class="left">Office</td>
		<td><?php echo $r["office"]?></td>
	</tr>
	<tr>
		<td class="left">Phone</td>
		<td><?php echo format_phone($r["phone"])?></td>
	</tr>
	<tr>
		<td class="left">Email</td>
		<td><a href="mailto:<?php echo $r["email"]?>"><?php echo $r["email"]?></a></td>
	</tr>
	<tr>
		<td class="left">Last Login</td>
		<td><?php echo format_date_time($r["lastlogin"], " ")?></td>
	</tr>
	<tr>
		<td class="left">Bio</td>
		<td colspan="2" height="167" class="text"><?php echo nl2br($r["bio"])?></td>
	</tr>
	<?php 
	if ($skills = db_table('SELECT s.group, s.title FROM users_to_skills u2s JOIN skills s ON u2s.skill_id = s.id WHERE u2s.user_id = ' . $_GET['id'] . ' AND s.isActive = 1 ORDER BY s.group, s.title')) {
		$groups = array_key_promote($skills, 'group');	
		?>
	<tr>
		<td class="left">Skills</td>
		<td colspan="2">
			<?php foreach ($groups as $group=>$skills) {?>
			<strong><?php echo $group?></strong>
			<ul class="nospacing">
			<?php foreach ($skills as $skill) {?>
			<li><?php echo $skill['title']?></li>
			<?php }?>
			</ul>
			<?php }?>
		</td>
	</tr>
	<?php }
	if ($isAdmin || ($_GET["id"] == $user["id"])) {?>
	<tr class="group">
		<td colspan="3">Intranet</td>
	</tr>
	<?php if ($r["longDistanceCode"]) {?>
	<tr>
		<td class="left">Telephone Code</td>
		<td colspan="2" class="bigger"><?php echo $r["longDistanceCode"]?></td>
	</tr>
	<?php }
	if ($r["startDate"]) {?>
	<tr>
		<td class="left">Start Date</td>
		<td colspan="2"><?php echo format_date($r["startDate"])?></td>
	</tr>
	<?php }
	if ($r["endDate"]) {?>
	<tr>
		<td class="left">End Date</td>
		<td colspan="2"><?php echo format_date($r["endDate"])?></td>
	</tr>
	<?php }
	if ($_GET["id"] == $user["id"]) {
		?>
		<tr>
			<td class="left">Password</td>
			<td colspan="2"><a href="<?php echo deleteLink("Reset password?", $_GET["id"], "passwd")?>" class="button" style="line-height:13px;">change your password</a></td>
		</tr>
		<?php } elseif ($isAdmin) {?>
		<tr>
			<td class="left">Password</td>
			<td colspan="2">
				<?php if ($r["password"]){?>
					<i>password is reset</i>
				<?php } else {?>
					<a href="<?php echo deleteLink("Reset password?", $_GET["id"], "passwd")?>" class="button" style="line-height:13px;">reset password</a>
				<?php }?>
			</td>
		</tr>
	<?php }?>
	<?php if ($isAdmin) {?>
	<tr>
		<td class="left">Invite</td>
		<td colspan="2"><a href="<?php echo deleteLink("Send email invite?", $_GET["id"], "invite")?>" class="button" style="line-height:13px;">re-invite user</a></td>
	</tr>
	<tr>
		<td class="left">Rank</td>
		<td colspan="2"><?php echo $r["rank"]?></td>
	</tr>
	<?php
	if ($permissions = db_table("SELECT 
			m.name,
			m.isPublic,
			p.url
			FROM modules m 
			JOIN pages p ON m.homePageID = p.id
			JOIN administrators a ON m.id = a.moduleID
			WHERE a.userID = {$_GET["id"]}
			ORDER BY m.name")) {?>
	<tr>
		<td class="left">Permissions</td>
		<td colspan="2">
			<ul>
		<?php foreach ($permissions as $p) {?>
			<li>
			<?php
			if ($p["isPublic"]) {
				echo "<a href='" . $p["url"] . "'>" . $p["name"] . '</a>';
			} else {
				echo $p["name"];			
			}
			?>
			</li>
		<?php }?>
			</ul>		
		</td>
	</tr>
	<?php }
	}?>
	<tr class="group">
		<td colspan="3">Home Contact Information [private]</td>
	</tr>
	<tr>
		<td class="left">Home Address</nobr></td>
		<td colspan="2"><?php echo $r["homeAddress1"]?><br>
			<?php if ($r["homeAddress2"]) {?><?php echo $r["homeAddress2"]?><br><?php }?>
			<?php echo $r["homeCity"]?>, <?php echo $r["stateAbbrev"]?> <?php echo $r["homeZIP"]?>
		</td>
	</tr>
	<tr>
		<td class="left">Home Phone</nobr></td>
		<td colspan="2"><?php echo format_phone($r["homePhone"])?></td>
	</tr>
	<tr>
		<td class="left">Cell Phone</td>
		<td colspan="2"><?php echo format_phone($r["homeCell"])?></td>
	</tr>
	<tr>
		<td class="left">Personal Email</td>
		<td colspan="2"><a href="mailto:<?php echo $r["homeEmail"]?>"><?php echo $r["homeEmail"]?></a></td>
	</tr>
	<tr class="group">
		<td colspan="3">Emergency Contact Information [private]</td>
	</tr>
	<tr>
		<td class="left"><?php echo $r["emerCont1Relationship"]?></td>
		<td colspan="2">
			<b><?php echo $r["emerCont1Name"]?></b><br>
			<?php if($r["emerCont1Phone"]) {?><?php echo format_phone($r["emerCont1Phone"])?><br><?php }?>
			<?php if($r["emerCont1Cell"]) {?><?php echo format_phone($r["emerCont1Cell"])?><br><?php }?>
			<?php echo $r["emerCont1Email"]?>
		</td>
	</tr>
	<tr>
		<td class="left"><?php echo $r["emerCont2Relationship"]?></td>
		<td colspan="2">
			<b><?php echo $r["emerCont2Name"]?></b><br>
			<?php if($r["emerCont2Phone"]) {?><?php echo format_phone($r["emerCont2Phone"])?><br><?php }?>
			<?php if($r["emerCont2Cell"]) {?><?php echo format_phone($r["emerCont2Cell"])?><br><?php }?>
			<?php echo $r["emerCont2Email"]?>
		</td>
	</tr>
	<?php }?>
</table>
<?php
drawBottom();