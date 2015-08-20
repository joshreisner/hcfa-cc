<?php
include('../include.php');
drawTop();

?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Former Staff", 5);?>
	<?php
	$staff = db_query("SELECT 
							u.userID, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone, 
							f.name office, 
							u.title, 
							d.departmentName
						FROM intranet_users u
						LEFT  JOIN intranet_departments d ON d.departmentID = u.departmentID 
						LEFT  JOIN intranet_offices f     ON f.id = u.officeID
						WHERE u.isactive = 0
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	while ($s = db_fetch($staff)) {?>
	<tr height="38">
		<td><?php echo drawImg($staff['userID'])?></td>
		<td><nobr><a href="view.php?id=<?php echo $s["userID"]?>"><?php echo $s["lastname"]?>, <?php echo $s["firstname"]?></a></nobr></td>
		<td><?php echo $s["title"]?></td>
		<td><?php echo $s["office"]?></td>
		<td align="right"><nobr><?php echo format_phone($s["phone"])?></nobr></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();