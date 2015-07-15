<?php
include("include.php");

if (isset($_GET["deleteID"])) {
	if (db_grab("SELECT endDate FROM intranet_users WHERE userID = " . $_GET["deleteID"])) {
		db_query("UPDATE intranet_users SET isActive = 0, deletedBy = {$user["id"]}, deletedOn = GETDATE() WHERE userID = " . $_GET["deleteID"]);
	} else {
		db_query("UPDATE intranet_users SET isActive = 0, deletedBy = {$user["id"]}, deletedOn = GETDATE(), endDate = GETDATE() WHERE userID = " . $_GET["deleteID"]);
	}
	url_query_drop("deleteID");
}

$orgs = array();
if ($locale == "/_seedco/") {
	if (!isset($_GET["id"])) $_GET["id"] = 1;
} else {
	if (!isset($_GET["id"])) $_GET["id"] = 0;
	$orgs[0] = "Shared";
}
$orgs = db_array("SELECT id, description FROM organizations ORDER BY description", $orgs);
drawTop();
?>
<table class="navigation staff" cellspacing="1">
	<tr class="staff-hilite">
		<?php foreach ($orgs as $key=>$value) {?>
		<td width="14.28%"<?php if ($_GET["id"] == $key) {?> class="selected"<?php }?>><?php if ($_GET["id"] != $key) {?><a href="organizations.php?id=<?php echo $key?>"><?php } else {?><b><?php }?><?php echo $value?></b></a></td>
		<?php }?>
	</tr>
</table>

<?php
$where = ($_GET["id"] == 0) ? " IS NULL " : " = " . $_GET["id"];
echo drawStaffList("u.isactive = 1 AND u.corporationID " . $where);

drawBottom();