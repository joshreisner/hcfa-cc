<?php
include('include.php');

if ($posting) {
	$id = db_enter('skills', 'title group');
	if ($editing) {
		url_change('skill.php?id=' . url_id());
	} else {
		url_change('skills.php');		
	}
}

echo drawTop();

if ($editing) {
	$r = db_grab('SELECT title, `group` FROM skills WHERE id = ' . $_GET['id']);
}

$form = new intranet_form;
$form->addRow('itext',  'Title', 'title', @$r['title'], '', true, 50);
$form->addRow('itext',  'Group', 'group', @$r['group'], '', true, 50);
$form->addRow('submit', $editing ? 'Save Changes' : 'Add Skill');
echo $form->draw('<a href="./">Staff</a> &gt; <a href="skills.php">Skills</a> &gt; ' . ($editing ? 'Edit Skill' : 'Add Skill'));

echo drawBottom();