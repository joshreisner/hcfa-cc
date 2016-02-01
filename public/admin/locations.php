<?php
include('../include.php');

if ($posting) {
	if (isset($_POST['order'])) {
		$counter = 1;
		foreach ($_POST['order'] as $id) {
			db_query('UPDATE intranet_offices SET precedence = ' . $counter . ' WHERE id = ' . $id);
			$counter++;
		}
		exit;
	} else {
		db_enter('intranet_offices', 'name address precedence');
		url_query_drop('id');
	}
} elseif (url_id() && url_action('delete')) {
	db_query('DELETE FROM intranet_offices WHERE id = ' . $_GET['id']);
	url_query_drop('action,id');
}

drawTop();

if (url_id()) {
	if (!$l = db_grab('SELECT id, name, address, precedence FROM intranet_offices WHERE id = ' . $_GET['id'])) {
		url_query_drop('id');
	}
	$form = new intranet_form;
	$form->addRow('hidden', '', 'precedence', $l['precedence']);
	$form->addRow('itext',  'Name' , 'name', $l['name'], '', true, 255);
	$form->addRow('textarea-plain',  'Address' , 'address', $l['address'], '');
	$form->addRow('submit', 'Save Changes');
	$form->draw('Edit Location');
} else {
	
	?>
	<table cellspacing='1' class='left draggable locations'>
		<thead>
			<?php echo drawHeaderRow(false, 4, 'new', '#bottom')?>
			<tr>
				<th class='reorder'></th>
				<th>Link</th>
				<th>Address</th>
				<th class='delete'></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$intranet_offices = db_query('SELECT id, name, address FROM intranet_offices ORDER BY precedence');
			if ($max = db_found($intranet_offices)) {
			while ($l = db_fetch($intranet_offices)) {?>
				<tr id='<?php echo $l['id']?>'>
					<td class='reorder'><i class='glyphicon glyphicon-menu-hamburger'></i></td>
					<td><a href="<?php echo url_query_add(array('id'=>$l['id']), false)?>"><?php echo $l['name']?></a></td>
					<td><?php echo nl2br($l['address'])?></td>
					<?php echo deleteColumn('Are you sure?', $l['id']);?>
				</tr>
			<?php }
			} else {
				echo drawEmptyResult('No intranet_offices entered in the system yet!', 4);
			}?>
		</tbody>
	</table>
	
	<a name='bottom'></a>
	<?php
	$form = new intranet_form;
	$form->addRow('hidden', '', 'precedence', ($max + 1));
	$form->addRow('itext',  'Name' , 'name', '', '', true, 255);
	$form->addRow('textarea-plain',  'Address' , 'address', '', '', true, 255);
	$form->addRow('submit'  , 'add new link');
	$form->draw('Add a New Location');
}

drawBottom();