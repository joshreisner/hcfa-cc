<?php include("../include.php");

drawTop();
echo drawTableStart();
$errors = db_query("SELECT 
		e.name,
		e.createdOn,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.instances
	FROM errors e
	JOIN intranet_users u ON e.createdOn = u.userID
	ORDER BY e.createdOn DESC");
while ($e = db_fetch($errors)) {?>
	<tr>
		<td><?php echo $e["name"]?></td>
		<td><?php echo $e["first"]?> <?php echo $e["last"]?></td>
		<td clas="r"><?php echo $e["createdOn"]?></td>
	</tr>
<?php }
echo drawTableEnd();
drawBottom();?>