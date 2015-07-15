<?php
$events = db_query("SELECT
			e.id, 
			e.title, 
			e.startDate,
			t.color
		FROM calendar_events e
		JOIN calendar_events_types t ON e.typeID = t.id
		WHERE e.startDate > GETDATE() AND e.isActive = 1 
		ORDER BY e.startDate ASC", 4);
while ($e = db_fetch($events)) {?>
<tr>
	<td width="70%"><a href="<?php echo $module["url"]?>event.php?id=<?php echo $e["id"]?>" class="block" style="background-color:<?php echo $e["color"]?>;"><?php echo $e["title"]?></a></td>
	<td width="30%" align="right"><?php echo format_date($e["startDate"])?></a></td>
</tr>
<?php }?>