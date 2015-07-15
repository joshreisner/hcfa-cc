<?php include("include.php");

if ($posting) {
	format_post_html("message");
	db_query("DELETE FROM it_system_status");
	db_query("INSERT INTO it_system_status ( message, updatedOn, updatedBy ) VALUES (
		{$_POST["message"]},
		GETDATE(),
		{$user["id"]}
	)");
	url_change("./");
}

drawTop();

?>
<script language="javascript">
<!--
function validate(form) {
	tinyMCE.triggerSave();
	/* if (form.message.value.length) return true;
	alert("please enter a status message");
	return false; */
}
//-->
</script>
<table class="left" cellspacing="1">
	<form action="<?php echo $request["path_query"]?>" method="post" onSubmit="javascript:return validate(this);">
	<?php echo drawHeaderRow("Update Status Message");?>
	<tr>
		<td><?php echo draw_form_textarea("message", $helpdeskStatus, "mceEditor full", false);?></td>
	</tr>
	<tr>
		<td class="bottom"><?php echo draw_form_submit("update message");?></td>
	</tr>
	</form>
</table>
<?php echo drawBottom();?>