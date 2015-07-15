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
							d.departmentName,
							u.imageID,
							m.height,
							m.width
						FROM intranet_users u
						LEFT  JOIN intranet_departments d ON d.departmentID = u.departmentID 
						LEFT  JOIN intranet_offices f     ON f.id = u.officeID
						LEFT  JOIN intranet_images m      ON u.imageID = m.imageID
						WHERE u.isactive = 0
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	while ($s = db_fetch($staff)) {?>
	<tr height="38">
		<?php if ($s["imageID"]) {
			verifyImage($s["imageID"]);
			$factor      = (31 / $s["height"]);
			$s["width"]  = $s["width"]  * $factor;
			$s["height"] = $s["height"] * $factor;
			?>
		<td width="47" align="center"><a href="/staff/view.php?id=<?php echo $s["userID"]?>"><img src="/data/staff/<?php echo $s["imageID"]?>.jpg" width="<?php echo $s["width"]?>" height="<?php echo $s["height"]?>" border="0"></a></td>
		<?php } else {?>
		<td>&nbsp;</td>
		<?php }?>
		<td><nobr><a href="view.php?id=<?php echo $s["userID"]?>"><?php echo $s["lastname"]?>, <?php echo $s["firstname"]?></a></nobr></td>
		<td><?php echo $s["title"]?></td>
		<td><?php echo $s["office"]?></td>
		<td align="right"><nobr><?php echo format_phone($s["phone"])?></nobr></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>