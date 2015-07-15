<?php include("include.php");

if ($posting) {

	$checkoutStart = format_post_date("checkoutStart");
	$checkoutEnd   = (isset($_POST["noEndDate"])) ? "NULL" : format_post_date("checkoutEnd");
	
	$id = db_query("INSERT INTO it_laptops_checkouts ( 
			checkoutUser, 
			checkoutStart, 
			checkoutEnd, 
			checkoutNotes,
			checkoutLaptopID
		) VALUES (
			{$_POST["checkoutUser"]},
			$checkoutStart,
			$checkoutEnd,
			'{$_POST["checkoutNotes"]}',
			{$_GET["id"]})");
	db_query("UPDATE IT_Laptops SET checkoutID = {$id}, laptopStatusID = 1 WHERE laptopID = " . $_GET["id"]);
	url_change("laptops.php");
}
		
drawTop();
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Check Laptop Out", 2);?>	
	<form method="post" action="<?php echo $request["path_query"]?>">
	<tr>
		<td class="left">Laptop</td>
		<td><b><a href="laptop.php?id=<?php echo $_GET["id"]?>"><?php echo db_grab("SELECT laptopName FROM IT_Laptops WHERE laptopID = " . $_GET["id"])?></a></b></td>
	</tr>
	<tr>
		<td class="left">User</td>
		<td><?php echo drawSelectUser("checkoutUser", false, false, false, true)?></td>
	</tr>
	<tr>
		<td class="left">Start</td>
		<td><?php echo draw_form_date("checkoutStart")?></td>
	</tr>
	<tr>
		<td class="left">End</td>
		<td>
			<table class="nospacing">
				<tr>
					<td><?php echo draw_form_date("checkoutEnd")?></td>
					<td>&nbsp;<?php echo draw_form_checkbox("noEndDate");?>&nbsp;</td>
					<td>(no end date)</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr valign="top">
		<td class="left">Notes</td>
		<td><?php echo draw_form_textarea("checkoutNotes")?></td>
	</tr>
	<tr>
		<td colspan="2" class="bottom"><?php echo draw_form_submit("check out"); ?></td>
	</tr>
	</form>
</table>
<?php drawBottom(); ?>