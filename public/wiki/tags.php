<?php
include("../include.php");

if ($posting) {
	$id = db_enter("wiki_tags", "description");
    url_change();
}

drawTop();

?>
<table class="left" cellspacing="1">
	<?php
	echo drawHeaderRow("Tags", 2);
	$tags = db_query("SELECT 
		t.id, 
		t.description,
		(SELECT COUNT(*) FROM wiki_topics_to_tags w2t WHERE w2t.tagID = t.id) topics
		FROM wiki_tags t 
		WHERE t.isActive = 1
		ORDER BY t.description");
	if (db_found($tags)) {?>
	<tr>
		<th>Tag</th>
		<th class="r">#</th>
	</tr>
	<?php while ($t = db_fetch($tags)) {?>
	<tr>
		<td><?php if ($t["topics"]) {?><a href="tag.php?id=<?php echo $t["id"]?>"><?php }?><?php echo $t["description"]?><?php if ($t["topics"]) {?></a><?php }?></td>
		<td align="right"><?php echo $t["topics"]?></td>
	</tr>
	<?php } 
	} else {
		echo drawEmptyResult("No tags have been entered yet.", 2);
	}?>
</table>
<?php if ($isAdmin && !$printing) {
	$form = new intranet_form;
	if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $user["id"], false, true);
	$form->addRow("itext",  "Tag" , "description", "", "", true, 255);
	$form->addRow("submit"  , "add tag");
	$form->draw("Add a New Tag");
}

drawBottom(); ?>