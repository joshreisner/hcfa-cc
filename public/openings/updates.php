<?php
include("../include.php");
drawTop();


$users = db_query("select 
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		u.userID,
		u.title,
		d.departmentName,
		u.officeid,
		r.isPayroll,
		u.lastlogin, 
		u.updatedOn, 
		" . db_datediff("u.updatedOn", "GETDATE()") . " recent 
	FROM intranet_users u
	JOIN intranet_departments d on u.departmentID = d.departmentID
	JOIN intranet_ranks r on u.rankID = r.id
	WHERE u.isactive = 1 and (u.homeaddress1 = '' OR u.homeaddress1 IS NULL)
	ORDER BY lastname");
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Staff Profiles Needing Update (" . db_found($users) . ")", 4);?>
	<tr>
		<th width="25%" align="left">email</th>
		<th width="30%" align="left">department</th>
		<th width="30%" align="left">title</th>
		<th width="15%" align="right">last login</th>
	</tr>
	<?php while ($u = db_fetch($users)) {?>
	<tr>
		<td><a href="/staff/view.php?id=<?php echo $u["userID"]?>"><?php echo $u["first"]?> <?php echo $u["last"]?></a></td>
		<td><?php echo $u["departmentName"]?>
		<td><?php echo $u["title"]?>
		<td align="right"><?php echo format_date($u["lastlogin"])?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>