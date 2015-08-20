<?php
include('include.php');

if (url_action('delete')) {
	die('delete');
}

echo drawTop();
echo drawTableStart();

if ($isAdmin) {
	echo drawHeaderRow('', 3, 'Add New', 'skill_add_edit.php');
	$colspan = 3;
} else {
	echo drawHeaderRow('', 2);
	$colspan = 2;
}

$lastGroup = '';
$skills = db_table('SELECT 
	s.id, 
	s.title, 
	s.group, 
	(SELECT COUNT(*) FROM users_to_skills u2s 
		JOIN intranet_users u ON u2s.user_id = u.userID
		WHERE u2s.skill_id = s.id AND u.isActive = 1) as count
	FROM skills s 
	WHERE s.isActive = 1 
	ORDER BY s.group, s.title');
?>
<tr>
	<th>Title</th>
	<th class="r">Count</th>
	<?php if ($isAdmin) {?><th></th><?php }?>
</tr>
<?php
foreach ($skills as $skill) {
	if ($lastGroup != $skill['group']) {
		echo '<tr class="group"><td colspan="' . $colspan . '">' . $skill['group'] . '</td></tr>';
		$lastGroup = $skill['group'];
	}
	echo '<tr>
		<td><a href="skill.php?id=' . $skill['id'] . '">' . $skill['title'] . '</a></td>
		<td class="r">' . number_format($skill['count']) . '</td>
		' . deleteColumn('Are You Sure?', $skill['id']) . '
	</tr>';
}

echo drawTableEnd();
echo drawBottom();