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
		$id = db_enter('spotlight', 'title url precedence');
		if ($uploading && (file_ext($_FILES["userfile"]['name']) == 'jpg')) {
			define('DIRECTORY_ROOT', $_SERVER['DOCUMENT_ROOT']);
			define('DIRECTORY_WRITE', '/uploads');
			$image = format_image($_FILES["userfile"]["tmp_name"], 'jpg');
			$image = format_image_resize($image, 320, 320);
			file_put('/uploads/spotlight/' . $id . '.jpg', $image);
		}
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
		<?php echo drawHeaderRow(false, 5, "new", "#bottom")?>
		<tr>
			<th class="reorder"></th>
			<th>Image</th>
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
				<td class="image">
					<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/spotlight/' . $s['id'] . '.jpg')) {?>
					<img src="/uploads/spotlight/<?php echo $s['id']?>.jpg" width="320" height="320">
					<?php }?>
				</td>
				<td><?php echo $s['title']?></td>
				<td><?php echo $s['url']?></td>
				<?php echo deleteColumn('Are you sure?', $s['id']);?>
			</tr>
		<?php }
		} else {
			echo drawEmptyResult('No spotlight stories entered in the system yet!', 5);
		}?>
	</tbody>
</table>

<a name="bottom"></a>
<?php
$form = new intranet_form;
$form->addRow('hidden', '', 'precedence', ++$max);
$form->addRow('itext',  'Title' , 'title', '', '', true, 255);
$form->addRow('itext',  'Link' , 'url', 'http://', '', true, 255);
$form->addRow("file", "Image", "userfile");
$form->addRow('submit'  , 'add new link');
$form->draw('Add a New Link');

drawBottom();