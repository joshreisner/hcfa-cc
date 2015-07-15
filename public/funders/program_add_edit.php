<?php  
include("../include.php");

if ($posting) {
	if ($editing) {
		db_query("UPDATE intranet_programs SET programDesc = '" . $_POST["programDesc"] . "' WHERE programID = " . $_GET["id"]);
	} else {
		$_GET["id"] = db_query("INSERT INTO intranet_programs ( programDesc ) VALUES ( '{$_POST["programDesc"]}' )");
	}
	url_change("program.php?id=" . $_GET["id"]);
}

drawTop();

if ($editing) {
	$program = db_grab("SELECT programDesc FROM intranet_programs WHERE programID = " . $_GET["id"]);
	$title = "Edit Program";
} else {
	$title = "Add New Program";
}
?>
<table class="left" cellspacing="1">
	<form method="post" action="<?php echo $_josh["request"]["path_query"]?>">
	<?php echo drawHeaderRow($title, 2);?>
	<tr>
		<td class="left">Name</td>
		<td><?php echo draw_form_text("programDesc", @$program)?></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?php echo draw_form_submit("save changes")?></td>
	</tr>
	</form>
</table>
<?php drawBottom();?>