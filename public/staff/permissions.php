<?php
include("../include.php");

if ($posting) { //update permissions
	db_query("DELETE FROM administrators WHERE userID = " . $_GET["id"]);
	foreach ($_POST as $key=>$value) {
		@list($control, $moduleID) = explode("_", $key);
		if ($control == "chk") db_query("INSERT INTO administrators ( userID, moduleID ) VALUES ( {$_GET["id"]}, {$moduleID} )");
	}
	url_change("view.php?id=" . $_GET["id"]);
}

drawTop();
drawNavigation();
$u = db_grab("SELECT ISNULL(u.nickname, u.firstname) first, u.lastname last FROM intranet_users u WHERE u.userID = " . $_GET["id"]);
?>
<table class="left" cellspacing="1">
	<form method="post" name="permissions" action="<?php echo $_josh["request"]["path_query"]?>">
	<?php echo drawHeaderRow("Edit Permissions",2);?>
	<tr>
		<td class="left">User</td>
		<td><b><?php echo $u["first"]?> <?php echo $r["last"]?></b></td>
	</tr>
	<tr>
		<td class="left">Permissions</td>
		<td><table class="nospacing">
		<?php
		$result = db_query("SELECT 
								m.id,
								m.name,
								(SELECT COUNT(*) FROM administrators a WHERE a.moduleID = m.id AND a.userID = {$_GET["id"]}) isAdmin
							FROM modules m
							WHERE m.isActive = 1
							ORDER BY m.name");
		while ($r = db_fetch($result)) {?>
				<tr>
					<td><?php echo draw_form_checkbox("chk_" . $r["id"], $r["isAdmin"])?></td>
					<td><?php echo $r["name"]?></td>
				</tr>
		<?php }?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?php echo draw_form_submit("save changes");?></td>
	</tr>
	</form>
</table>
<?php drawBottom();?>