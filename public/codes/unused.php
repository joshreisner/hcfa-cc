<?php include("../include.php");

drawTop();
?>
<table class="left">
	<?php echo drawHeaderRow("Long Distance Codes", 1)?>
	<?php
	$codes = db_query("SELECT
	l.code
FROM ldcodes l
WHERE (SELECT COUNT(*) FROM intranet_users u WHERE u.isactive = 1 AND u.officeID = 1 AND u.longdistancecode = l.code) = 0
ORDER BY NEWID()");
	while ($c = db_fetch($codes)) {?>
	<tr>
		<td><?php echo sprintf("%04s", $c["code"]);?></td>
	</tr>
	<?php }?>
</table>

<?php drawBottom();?>