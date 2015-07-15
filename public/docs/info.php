<?php
include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE documents SET isActive = 0, deletedOn = GETDATE(), deletedBy = {$user["id"]} WHERE id = " . $_GET["id"]);
	url_change("/docs/");
}

$d = db_grab("SELECT 
		d.name,
		d.description,
		d.content,
		i.icon,
		i.description fileType
	FROM documents d
	JOIN intranet_doctypes i ON d.typeID = i.id
	WHERE d.id = " . $_GET["id"]);

drawTop();

?>

<table class="left" cellspacing="1">
    <?php
    if ($isAdmin) {
    	echo drawHeaderRow("Document Info", 2, "edit","add_edit.php?id=" . $_GET["id"], "delete", deleteLink("Delete document?"));
    } else {
    	echo drawHeaderRow("Document Info", 2);
    }
    ?>
	<tr>
		<td class="left">Name</td>
		<td><h1><a href="download.php?id=<?php echo $_GET["id"]?>"><?php echo $d["name"]?></h1></a></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><table class="nospacing"><tr>
			<td><?php echo draw_img($locale . $d["icon"])?></td>
			<td><?php echo $d["fileType"]?> (<?php echo format_size(strlen($d["content"]))?>)</td>
			</tr></table>
		</td>
	</tr>
	<tr>
		<td class="left">Categories</td>
		<td>
			<?php $categories = db_query("SELECT
				c.description
			FROM documents_to_categories d2c
			JOIN documents_categories c ON d2c.categoryID = c.id
			WHERE d2c.documentID = " . $_GET["id"]);
				while ($c = db_fetch($categories)) {?>
				 &#183; <?php echo $c["description"]?></a><br>
			<?php }?>
		</td>
	</tr>
	<tr height="120">
		<td class="left">Description</td>
		<td class="text"><?php echo nl2br($d["description"])?></td>
	</tr>
</table>
<?php
$views = db_query("SELECT 
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.userID,
			v.viewedOn
			FROM documents_views v
			JOIN intranet_users u ON v.userID = u.userID
			WHERE v.documentID = " . $_GET["id"] . "
			ORDER BY v.viewedOn DESC", 5);
if (db_found($views)) {?>
<table class="left" cellspacing="1">
    <tr>
		<td class="head docs" colspan="2">Recent Views</td>
	</tr>
	<tr class="left">
		<th align="left">Name</th>
		<th align="right">Date</th>
	</tr>
	<?php while($v = db_fetch($views)) {?>
	<tr>
		<td width="70%"><a href="/staff/view.php?id=<?php echo $v["userID"]?>"><?php echo $v["first"]?> <?php echo $v["last"]?></a></td>
		<td width="30%" align="right"><?php echo format_date_time($v["viewedOn"], " ")?></td>
	</tr>
	<?php }?>
</table>
<?php 
}
drawBottom();?>