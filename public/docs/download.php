<?
include("../include.php");

$d = db_grab("SELECT 
		d.name, 
		t.extension, 
		d.content 
	FROM documents d 
	JOIN intranet_doctypes t ON d.typeID = t.id
	WHERE d.id = " . $_GET["id"]);

db_query("INSERT INTO documents_views ( documentID, userID, viewedOn ) VALUES ( {$_GET["id"]}, {$user["id"]}, GETDATE() )");

file_download($d["content"], $d["name"], $d["extension"])
?>