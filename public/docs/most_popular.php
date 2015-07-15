<?php
include("../include.php");
drawTop();

?>
<table class="left" cellspacing="1">
	<?php echo drawheaderRow("Most Popular", 3);?>
	<tr>
		<th width="16"></th>
		<th width="85%" align="left">Name</th>
		<th width="15%" align="right">Views</th>
	</tr>
	<?php
	$result = db_query("SELECT 
			d.id,
			d.name,
			(SELECT COUNT(*) FROM documents_views v WHERE v.documentID = d.id) downloads,
			i.icon,
			i.description alt
		FROM documents d
		JOIN intranet_doctypes i ON d.typeID = i.id
		WHERE d.isActive = 1
		ORDER BY downloads DESC", 20);
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="info.php?id=<?php echo $r["id"]?>"><img src="<?php echo $locale?><?php echo $r["icon"]?>" width="16" height="16" border="0" alt="<?php echo $r["alt"]?>"></a></td>
		<td><a href="info.php?id=<?php echo $r["id"]?>"><?php echo $r["name"]?></a></td>
		<td align="right"><?php echo number_format($r["downloads"])?></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom();?>