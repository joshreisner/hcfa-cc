<?php include("../include.php");

if ($posting) {
	format_post_bits("isInstancePage, isSecure, isAdmin");
	db_query("UPDATE pages SET 
		name = '{$_POST["title"]}',
		isAdmin = {$_POST["isAdmin"]},
		precedence = {$_POST["precedence"]},
		isInstancePage = {$_POST["isInstancePage"]},
		isSecure = {$_POST["isSecure"]},
		moduleID = '{$_POST["moduleID"]}',
		helpText = '{$_POST["helpText"]}'
		WHERE id = " . $_GET["id"]);
	url_change($_POST["returnTo"]);
}

drawTop();

$r = db_grab("SELECT
	p.id,
	p.name title,
	p.helpText,
	m.id moduleID,
	m.name module,
	p.isAdmin,
	p.isSecure,
	p.precedence,
	p.isInstancePage,
	p2.url
	FROM pages p
	JOIN modules m ON p.moduleID = m.id
	JOIN pages p2 ON m.homePageID = p2.id
	WHERE p.id = " . $_GET["id"]);

$form = new intranet_form;
$form->addRow("hidden",  "", "returnTo", $_GET["returnTo"]);
$form->addRow("itext",  "Title", "title", $r["title"], "", true, 50);
$form->addRow("itext",  "Precedence", "precedence", $r["precedence"], "", true, 50);
$form->addRow("checkbox",  "Is Admin", "isAdmin", $r["isAdmin"], "", true, 50);
$form->addRow("checkbox",  "Is Instance Page", "isInstancePage", $r["isInstancePage"], "", true, 50);
$form->addRow("checkbox",  "Is Secure", "isSecure", $r["isSecure"], "", true, 50);
$form->addRow("select", "Module", "moduleID", "SELECT id, name FROM modules WHERE isActive = 1 ORDER BY name", $r["moduleID"], $r["moduleID"]);
//$form->addRow("text", "Module", "", "<span class='" . str_replace("/", "", $r["url"]) . " block'>" . $r["module"] . "</span>");
$form->addRow("textarea", "Help Text", "helpText", $r["helpText"]);
$form->addRow("submit",   "Save Changes");
$form->draw("Edit Page Info");


drawBottom();?>