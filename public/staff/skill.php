<?php
include('include.php');

url_query_require('skills.php');

if (!$skill = db_grab('SELECT title FROM skills where isActive = 1 AND id = ' . url_id())) {
	url_chagne('skills.php');
}

echo drawTop();
echo drawTableStart();

if ($isAdmin) {
	echo drawHeaderRow($skill, 1, 'Edit', 'skill_add_edit.php?id=' . url_id());
} else {
	echo drawHeaderRow($skill, 1);	
}

if ($users = db_table('SELECT 
		u.userID, 
		u.firstName,
		u.lastName 
	FROM users_to_skills u2s 
	JOIN intranet_users u ON u2s.user_id = u.userID
	WHERE u2s.skill_id = ' . url_id() . ' AND u.isActive = 1
	ORDER BY u.lastName, u.firstName')) {?>
	<tr>
		<th>User</th>
	</tr>
	<?php
	foreach ($users as $u) {
		echo '<tr>
			<td><a href="view.php?id=' . $u['userID'] . '">' . $u['firstName'] . ' ' . $u['lastName'] . '</a></td>
		</tr>';
	}
} else {
	echo drawEmptyResult('No users are tagged with this skill.');
}

echo drawTableEnd();
echo drawBottom();