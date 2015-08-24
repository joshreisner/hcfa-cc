<?php
include("../include.php");

if ($posting) {
	if (isset($_POST['order'])) {
		$counter = 1;
		foreach ($_POST['order'] as $link_id) {
			db_query('UPDATE links SET precedence = ' . $counter . ' WHERE id = ' . $link_id);
			$counter++;
		}
		die(drawLinks()); //for ajax to update links area in sidebar
	} else {
		db_enter("links", "text url precedence");
		url_change();
	}
} elseif (!empty($_GET['id']) && url_action('delete')) {
	db_query('DELETE FROM links WHERE id = ' . $_GET['id']);
	url_query_drop('action,id');
}

drawTop();
?>
<table cellspacing="1" class="left draggable links">
	<thead>
		<?php echo drawHeaderRow(false, 4, "new", "#bottom")?>
		<tr>
			<th class="reorder"></th>
			<th>Link</th>
			<th>Address</th>
			<th class="delete"></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$links = db_query("SELECT id, text, url FROM links ORDER BY precedence");
		if ($max = db_found($links)) {
		while ($l = db_fetch($links)) {?>
			<tr id="<?php echo $l['id']?>">
				<td class="reorder"><i class="glyphicon glyphicon-menu-hamburger"></i></td>
				<td><?php echo $l["text"]?></td>
				<td><?php echo $l["url"]?></td>
				<?php echo deleteColumn('Are you sure?', $l['id']);?>
			</tr>
		<?php }
		} else {
			echo drawEmptyResult("No links entered in the system yet!", 4);
		}?>
	</tbody>
</table>

<a name="bottom"></a>
<?php
$form = new intranet_form;
$form->addRow("hidden", "", "precedence", ($max + 1));
$form->addRow("itext",  "Link" , "text", "", "", true);
$form->addRow("itext",  "Address" , "url", "http://", "", true);
$form->addRow("submit"  , "add new link");
$form->draw("Add a New Link");

drawBottom();