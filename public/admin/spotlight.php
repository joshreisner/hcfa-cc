<?php
include("../include.php");

if ($posting) {
	if (isset($_POST['order'])) {
		$counter = 1;
		foreach ($_POST['order'] as $link_id) {
			db_query('UPDATE spotlight SET precedence = ' . $counter . ' WHERE id = ' . $link_id);
			$counter++;
		}
		die(drawSpotlight());
	} else {
		db_enter("spotlight", "title url precedence");
		url_change();
	}
} elseif (!empty($_GET['id']) && url_action('delete')) {
	db_query('DELETE FROM spotlight WHERE id = ' . $_GET['id']);
	url_query_drop('action,id');
}

drawTop();
?>
<table cellspacing="1" class="left draggable spotlight">
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
		$spotlight = db_query("SELECT id, title, url FROM spotlight ORDER BY precedence");
		if ($max = db_found($spotlight)) {
		while ($s = db_fetch($spotlight)) {?>
			<tr id="<?php echo $s['id']?>">
				<td class="reorder"><i class="glyphicon glyphicon-menu-hamburger"></i></td>
				<td><?php echo $s['title']?></td>
				<td><?php echo $s['url']?></td>
				<?php echo deleteColumn('Are you sure?', $s['id']);?>
			</tr>
		<?php }
		} else {
			echo drawEmptyResult('No spotlight stories entered in the system yet!', 4);
		}?>
	</tbody>
</table>

<a name="bottom"></a>
<?php
$form = new intranet_form;
$form->addRow('hidden', '', 'precedence', ++$max);
$form->addRow('itext',  'Title' , 'title', '', '', true, 255);
$form->addRow('itext',  'Link' , 'url', 'http://', '', true, 255);
$form->addRow('submit'  , 'add new link');
$form->draw('Add a New Link');

drawBottom();