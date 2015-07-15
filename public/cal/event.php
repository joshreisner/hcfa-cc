<?php
include("include.php");

$e = db_grab("SELECT 
		e.title, 
		e.description, 
		e.startDate, 
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.createdBy,
		e.createdOn,
		t.color,
		t.description type,
		MONTH(e.startDate) month, 
		YEAR(e.startDate) year,
		u.imageID,
		i.width imgwidth,
		i.height imgheight
	FROM calendar_events e
	JOIN intranet_users u ON e.createdBy = u.userID
	JOIN calendar_events_types t ON e.typeID = t.id
	LEFT JOIN intranet_images i ON u.imageID = i.imageID
	WHERE e.id = " . $_GET["id"]);
	
if (url_action("delete")) {
	db_query("DELETE FROM calendar_events WHERE id = " . $_GET["id"]);
	url_change("/cal/?month="  . $e["month"] . "&year=" . $e["year"]);
}

drawTop();
echo drawNavigationCal($e["month"], $e["year"], true)
?>
<table class="left" cellspacing="1">
	<?php
	if ($isAdmin) {
		echo drawHeaderRow("Event Details", 2, "edit", "event_edit.php?id=" . $_GET["id"], "delete", url_query_add(array("action"=>"delete"), false));
	} elseif ($user["id"] == $e["createdBy"]) {
		echo drawHeaderRow("Event Details", 2, "edit", "event_edit.php?id=" . $_GET["id"], "delete", url_query_add(array("action"=>"delete"), false));
	} else {
		echo drawHeaderRow("Event Details", 2);
	}?>
	<tr>
		<td class="left">Title</td>
		<td class="right" bgcolor="#ffffff"><b><?php echo $e["title"]?></b></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><span class="block" style="background-color:<?php echo $e["color"]?>;"><?php echo $e["type"]?></span></td>
	</tr>
	<tr>
		<td class="left">Start Date</td>
		<td><?php echo format_date_time($e["startDate"])?></td>
	</tr>
	<tr valign="top">
		<td class="left" height="200">Description</td>
		<td class="text"><?php echo $e["description"]?></td>
	</tr>
	<tr valign="top">
		<td class="left">Created</td>
		<td><?php echo drawName($e["createdBy"], $e["first"] . " " . $e["last"], $e["imageID"], $e["imgwidth"], $e["imgheight"], $e["createdOn"], true);?></td>
	</tr>
</table>
<?php drawBottom();?>