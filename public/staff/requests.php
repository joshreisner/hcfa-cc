<?php include("include.php");

if (url_action("deletereq")) {
	db_query("DELETE FROM users_requests WHERE id = " . $_GET["id"]);
	url_query_drop("action,id");
} elseif (url_action("invite")) {
	$result = db_query("SELECT userID, nickname, email, firstname FROM intranet_users WHERE lastlogin IS NULL AND isactive = 1");
	while ($r = db_fetch($result)) {
		$name = (!$r["nickname"]) ? $r["firstname"] : $r["nickname"];
		email_invite($r["userID"], $r["email"], $name);
	}
	url_query_drop("action");
}

drawTop();
echo drawJumpToStaff();
echo drawTableStart();
echo drawHeaderRow("", 3);
$result = db_query("SELECT id, lastname, firstname, createdOn FROM users_requests ORDER BY createdOn DESC");
if (db_found($result)) {?>
	<tr>
		<th width="70%">Name</th>
		<th width="30%" class="r">Invited On</th>
		<th></th>
	</tr>
	<?php while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="add_edit.php?requestID=<?php echo $r["id"]?>"><?php echo $r["lastname"]?>, <?php echo $r["firstname"]?></a></td>
		<td class="r"><?php echo format_date_time($r["createdOn"])?></td>
		<td width="16"><?php echo draw_img($locale . "images/icons/delete.gif", url_query_add(array("action"=>"deletereq", "id"=>$r["id"]), false))?></td>
	</tr>
	<?php
	}
} else {
	echo drawEmptyResult("No pending requests!");
}
echo drawTableEnd();

echo drawTableStart();
echo drawHeaderRow("Never Logged In", 3, "invite them all", url_query_add(array("action"=>"invite"), false));
$result = db_query("SELECT userid, lastname, firstname, createdOn FROM intranet_users WHERE lastlogin IS NULL AND isactive = 1 ORDER BY lastname");
if (db_found($result)) {?>
	<tr>
		<th width="70%">Name</th>
		<th width="30%" class="r">Created Date</th>
		<th></th>
	</tr>
	<?php
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="view.php?id=<?php echo $r["userid"]?>"><?php echo $r["lastname"]?>, <?php echo $r["firstname"]?></a></td>
		<td class="r"><?php echo format_date_time($r["createdOn"])?></td>
		<?php echo deleteColumn("Delete user?", $r["userid"])?>
	</tr>
	<?php
	}
} else {
	echo drawEmptyResult("No pending requests!");
}
echo drawTableEnd();

drawBottom();
?>