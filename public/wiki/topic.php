<?php
include("../include.php");

if (isset($_GET["deleteID"])) { //delete topic
	db_query("UPDATE wiki_topics SET isActive = 0, deletedby = {$user["id"]}, deletedOn = GETDATE() WHERE id = " . $_GET["deleteID"]);
	url_change("./");
}

url_query_require();

if ($uploading) { //upload an attachment
	$type = getDocTypeID($_FILES["userfile"]["name"]);
	$content = format_binary(file_get_contents($_FILES["userfile"]["tmp_name"]));
	unlink($_FILES["userfile"]["tmp_name"]);
	db_query("INSERT INTO wiki_topics_attachments (
		topicID,
		typeID,
		title,
		content,
		createdOn,
		createdBy
	) VALUES (
		{$_GET["id"]},
		{$type},
		'{$_POST["title"]}',
		$content,
		GETDATE(),
		{$user["id"]}
	)");
	url_change();
} elseif ($posting) { //add a comment
	$_POST["description"] = format_html($_POST["message"]);
	$_POST["topicID"] = $_GET["id"];
	$editing = false;
	$id = db_enter("wiki_topics_comments", "topicID description");
	url_change();
}

drawTop();


//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query("SELECT description, extension FROM intranet_doctypes ORDER BY description");
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t["extension"] . '")';
	$doctypes[] = " - " . $t["description"] . " (." . $t["extension"] . ")";
}

$t = db_grab("SELECT 
		w.title,
		w.description,
		w.typeID,
		(SELECT COUNT(*) FROM wiki_topics_attachments a WHERE a.topicID = w.id) hasAttachments,
		t.description type,
		w.isActive,
		w.createdOn,
		w.createdBy,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		u.imageID,
		m.width,
		m.height
	FROM wiki_topics w
	JOIN wiki_topics_types t ON w.typeID = t.id
	JOIN intranet_users u ON w.createdBy = u.userID
	LEFT JOIN intranet_images m ON u.imageID = m.imageID
	WHERE w.id = " . $_GET["id"]);
?>
<script language="javascript">
	<!--
	function validate(form) {
		if (!form.title.value.length) {
			alert("Please enter a name for the attachment.");
			return false;
		}
		if (!form.userfile.value.length) {
			alert("Please select a file to upload.");
			return false;
		} else {
			var arrFile   = form.userfile.value.split(".");
			var extension = arrFile[arrFile.length - 1].toLowerCase();
			if (<?php echo implode(" && ", $extensions)?>) {
				alert("Only these filetypes are supported by this system:\n\n <?php echo implode("\\n", $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filename.");
				return false;
			}
		}
		
		return true;
	}
	function validateComment(form) {
		if (!form.description.value.length || (form.description.value == '<p>&nbsp;</p>')) return false;
		return true;
	}
	//-->
</script>
	<?php
	echo drawTableStart();
	if ($isAdmin) {
		echo drawHeaderRow("View Topic", 2, "edit", "topic_edit.php?id=" . $_GET["id"], "delete", "topic_edit.php?deleteID=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Topic", 2);
	}?>
	<tr>
		<td class="left">Type</td>
		<td><a href="type.php?id=<?php echo $t["typeID"]?>"><?php echo $t["type"]?></a></td>
	</tr>
	<tr>
		<td class="left">Tags</td>
		<td>
		<?php
		$result = db_query("SELECT 
				t.id,
				t.description
			FROM wiki_tags t
			WHERE (SELECT COUNT(*) FROM wiki_topics_to_tags w2t WHERE w2t.topicID = " . $_GET["id"] . " AND w2t.tagID = t.id) > 0
			ORDER BY t.description");
		if (db_found($result)) {
			while ($r = db_fetch($result)) {
				$tags[] = '<a href="tag.php?id=' . $r["id"] . '">' . $r["description"] . '</a>';
			}
			echo implode(", ", $tags);
		} else {
			echo "<i>untagged</i>";
		}?>
		</td>
	</tr>
	<?php if ($t["hasAttachments"]) {?>
	<tr>
		<td class="left">Attachments</td>
		<td>
		<table class="nospacing">
		<?php
				$attachments = db_query("SELECT
				a.id,
				a.title,
				t.icon,
				t.description type
			FROM wiki_topics_attachments a
			JOIN intranet_doctypes t ON a.typeID = t.id
			WHERE a.topicID = " . $_GET["id"]);
		while ($a = db_fetch($attachments)) {?>
			<tr height="21">
				<td width="18"><a href="download.php?id=<?php echo $a["id"]?>"><img src="<?php echo $locale?><?php echo $a["icon"]?>" width="16" height="16" border="0"></a></td>
				<td><a href="download.php?id=<?php echo $a["id"]?>"><?php echo $a["title"]?></a></td>
			</tr>
		<?php } ?>
		</table>
		</td>
	</tr>
	<?php } 
	echo drawThreadTop($t["title"], $t["description"], $t["createdBy"], $t["first"] . " " . $t["last"], $t["imageID"], $t["width"], $t["height"], $t["createdOn"]);
		$comments = db_query("SELECT 
				c.id, 
				c.description,
				c.createdOn,
				c.createdBy,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				u.imageID,
				m.width,
				m.height
			FROM wiki_topics_comments c
			JOIN intranet_users u ON c.createdBy = u.userID
			LEFT JOIN intranet_images m ON u.imageID = m.imageID
			WHERE c.topicID = {$_GET["id"]}
			ORDER BY c.createdOn ASC");
		while ($c = db_fetch($comments)) {
			echo drawThreadComment($c["description"], $c["createdBy"], $c["first"] . " " . $c["last"], $c["imageID"], $c["width"], $c["height"], $c["createdOn"]);
		}
		echo drawThreadCommentForm();
	echo drawTableEnd();

if (!$printing && $isAdmin) {?>
<table class="left">
	<?php echo drawHeaderRow("Attach Document", 2);?>
	<form enctype="multipart/form-data" action="<?php echo $_josh["request"]["path_query"]?>" method="post" onsubmit="javascript:return validateComment(this);">
	<tr>
		<td class="left">Document Name</td>
		<td><?php echo draw_form_text("title",  @$d["name"])?></td>
	</tr>
	<tr>
		<td class="left">File</td>
		<td><input type="file" name="userfile" size="40" class="field" value=""></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?php echo draw_form_submit("Attach Document");?></td>
	</tr>
	</form>
</table>
<?php }
drawBottom();?>