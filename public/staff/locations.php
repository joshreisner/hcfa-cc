<?php
include('include.php');

if (!url_id()) url_query_add(array('id'=> 1));

drawTop();
$locations = db_query("SELECT 
		o.id, 
		o.name
	FROM intranet_offices o 
	ORDER BY (SELECT COUNT(*) FROM intranet_users u WHERE u.officeID = o.id) DESC");
	
if (db_found($locations)) {
	$pages = array();
	while ($l = db_fetch($locations)) {
		$pages["/staff/locations.php?id=" . $l["id"]] = $l["name"];
	}
	echo drawNavigationRow($pages, $location, true);
}

if ($_GET["id"] == "other") {
	echo drawStaffList("u.isactive = 1 AND u.officeID <> 1 AND u.officeID <> 6 AND u.officeID <> 11 AND u.officeID <> 9");
} else {
	$l = db_grab('SELECT name, address FROM intranet_offices WHERE id = ' . $_GET['id']);
	if (!empty($l['address'])) echo drawServerMessage('<center><strong>' . $l['name'] . ' Office</strong><br>' . nl2br($l['address']) . '</center>');
	echo drawStaffList("u.isactive = 1 and u.officeID = " . $_GET["id"]);
}

drawBottom();