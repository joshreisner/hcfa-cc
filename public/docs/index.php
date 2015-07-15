<?php
include('../include.php');

if (url_action("delete")) {
	db_query("UPDATE documents SET isActive = 0, deletedOn = GETDATE(), deletedBy = {$user["id"]} WHERE id = " . $_GET["id"]);
	url_drop();
}

drawTop();

?>
<table class="left" cellspacing="1">
    <?php if ($isAdmin) {
    	$colspan = 4;
	    echo drawHeaderRow("List", $colspan, "add", "add_edit.php");
    } else {
    	$colspan = 4;
	    echo drawHeaderRow("List", $colspan);
    }?>
	<tr>
		<th></th>
		<th align="left">Name, Description</th>
		<th align="right">Updated</th>
		<?php if ($isAdmin) {?><th></th><?php }?>
	</tr>
    <?php
    $categories = db_query("SELECT 
    			c.id, 
    			c.description, 
    			(SELECT COUNT(*) FROM documents_to_categories d2c JOIN documents d ON d2c.documentID = d.id WHERE d2c.categoryID = c.id AND d.isActive = 1) documents 
    		FROM documents_categories c
    		ORDER BY c.precedence");
	while ($c = db_fetch($categories)) {
		if (!$c["documents"]) continue;
		?>
		<tr class="group">
			<td colspan="<?php echo $colspan?>"><?php echo $c["description"]?></td>
		</tr>
		<?php $documents = db_query("SELECT 
							d.id, 
							d.name, 
							d.description,
							ISNULL(d.updatedOn, d.createdOn) updatedOn,
							i.icon, 
							i.description alt
						FROM documents d
						JOIN documents_to_categories d2c ON d.id = d2c.documentID
						JOIN intranet_doctypes i ON d.typeID = i.id
						WHERE d2c.categoryID = " . $c["id"] . "
						AND d.isActive = 1
						ORDER BY d.name;");
				while ($d = db_fetch($documents)) {?>
		<tr>
			<td width="16"><a href="info.php?id=<?php echo $d["id"]?>"><img src="<?php echo $locale?><?php echo $d["icon"]?>" width="16" height="16" border="0" alt="<?php echo $d["alt"]?>"></a></td>
			<td class="text2"><a href="info.php?id=<?php echo $d["id"]?>"><?php echo $d["name"]?></a></td>
			<td align="right"><?php echo format_date($d["updatedOn"])?></td>
			<?php echo deleteColumn("Delete document?", $d["id"]);?>
		</tr>
	<?php }
} ?>
</table>
<?php drawBottom(); ?>