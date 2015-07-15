<?php
$result = db_query("SELECT
		d.id, d.name, 
		ISNULL(d.updatedOn, d.createdOn) updatedOn,
		i.icon, i.description, (SELECT COUNT(*) FROM documents_views v WHERE v.documentID = d.id) downloads
		FROM documents d
	JOIN intranet_doctypes i ON d.typeID = i.id
	WHERE isActive = 1
	ORDER BY downloads DESC", 4);
while ($r = db_fetch($result)) {?>
<tr>
	<td width="16"><a href="<?php echo $module["url"]?>download.php?id=<?php echo $r["id"]?>"><img src="<?php echo $locale?><?php echo $r["icon"]?>" width="16" height="16" border="0" alt="<?php echo $r["description"]?>"></a></td>
	<td width="99%">
		<div style="float:right;"><nobr><?php echo format_date($r["updatedOn"])?></nobr></div>
		<a href="<?php echo $module["url"]?>download.php?id=<?php echo $r["id"]?>"><?php echo format_text_shorten($r["name"], 30);?></a>
	</td>
</tr>
<?php }?>