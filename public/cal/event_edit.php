<?php
include("include.php");

if ($posting) {
	$id = db_enter("calendar_events", "title description *startDate typeID");
	url_change("./event.php?id=" . $_GET["id"]);
}

$e = db_grab("SELECT 
		e.title, 
		e.description, 
		e.startDate, 
		e.typeID,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.createdBy,
		e.createdOn,
		MONTH(e.startDate) month, 
		YEAR(e.startDate) year
	FROM calendar_events e
	JOIN intranet_users u ON e.createdBy = u.userID
	WHERE e.id = " . $_GET["id"]);
	
drawTop();
echo drawNavigationCal($e["month"], $e["year"], true);

$form = new intranet_form;
if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $user["id"], $e["createdBy"], true);
$form->addRow("itext",  "Title" , "title", $e["title"], "", true);
$form->addRow("select", "Type", "typeID", "SELECT id, description FROM calendar_events_types ORDER BY description", $e["typeID"], true);
$form->addRow("datetime", "Date", "startDate", $e["startDate"]);
$form->addRow("textarea", "Notes" , "description", $e["description"], "", true);
$form->addRow("submit"  , "save changes");
$form->draw("Edit Event");

drawBottom();