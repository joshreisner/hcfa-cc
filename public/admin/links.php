<?php
include("../include.php");

if ($posting) {
	db_enter("links", "text url precedence");
	url_change();
}

drawTop();

echo drawTableStart();
echo drawHeaderRow(false, 5, "new", "#bottom");?>
<tr>
	<th style="text-align:left;">Link</th>
	<th style="text-align:left;">Address</th>
	<th style="width:16px;"></th>
	<th style="width:16px;"></th>
	<th style="width:16px;"></th>
</tr>
<?php
$links = db_query("SELECT id, text, url FROM links ORDER BY precedence");
if ($max = db_found($links)) {
while ($l = db_fetch($links)) {?>
	<tr>
		<td><?php echo $l["text"]?></td>
		<td><?php echo $l["url"]?></td>
		<td><img src="/images/icons/moveup.gif" width="16" height="16" border="0"></td>
		<td><img src="/images/icons/movedown.gif" width="16" height="16" border="0"></td>
		<?php echo deleteColumn();?>
	</tr>
<?php }
} else {
	echo drawEmptyResult("No links entered in the system yet!");
}
echo drawTableEnd();

echo '<a name="bottom"></a>';
$form = new intranet_form;
$form->addRow("hidden", "", "precedence", ($max + 1));
$form->addRow("itext",  "Link" , "text", "", "", true);
$form->addRow("itext",  "Address" , "url", "http://", "", true);
$form->addRow("submit"  , "add new link");
$form->draw("Add a New Link");

drawBottom(); 
?>

drawBottom();?>