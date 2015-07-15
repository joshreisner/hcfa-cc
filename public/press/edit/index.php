<?php
include("../../include.php");

if ($posting) {
	$theUserID = ($isAdmin) ? $_POST["createdBy"] : $user["id"];
	db_query("UPDATE intranet_press_releases SET
			headline       = '{$_POST["headline"]}',	
			detail         = '{$_POST["detail"]}',	
			location       = '{$_POST["location"]}',	
			text           = '" . format_html($_POST["text"]) . "',	
			corporationID = {$_POST["corporationID"]},
			updatedOn     = GETDATE(),
			updatedBy     = {$theUserID}
			WHERE id = " . $_GET["id"]);
	url_change("../?id=" . $_GET["id"]);
}

drawTop();

$r = db_grab("SELECT id, headline, detail, location, releaseDate, corporationID, text FROM intranet_press_releases WHERE id = " . $_GET["id"]);
	
$form = new intranet_form;
if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $user["id"], false, "EEDDCC");
$form->addRow("itext",  "Headline" , "headline", $r["headline"], "", true, 255);
$form->addRow("itext",  "Detail" , "detail", $r["detail"], "", false, 255);
$form->addRow("itext",  "Location" , "location", $r["location"], "", true, 255);
$form->addRow("select", "Organization" , "corporationID", "SELECT id, description FROM organizations ORDER BY description", $r["corporationID"]);
$form->addRow("date",  "Date" , "releaseDate", $r["releaseDate"]);
$form->addRow("textarea", "Text" , "text", $r["text"], "", true);
$form->addRow("submit"  , "update press release");
$form->draw("Update Release");

drawBottom();?>