<?php
include("../include.php");

if ($posting) {
	$id = db_enter("wiki_topics", "title typeID description");
	db_checkboxes("tags", "wiki_topics_to_tags", "topicID", "tagID", $id);
    url_change();
}

drawTop();

?>
<table class="left" cellspacing="1">
	<?php
	echo drawHeaderRow("Main Page", 4);
	$topics = db_query("SELECT 
		w.id,
		w.title,
		w.description,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		w.createdOn
	FROM wiki_topics w
	JOIN wiki_topics_types t ON w.typeID = t.id
	JOIN intranet_users u ON w.createdBy = u.userID
	WHERE w.isActive = 1
	ORDER BY w.createdOn DESC");
	if (db_found($topics)) {?>
	<tr>
		<th width="16"></th>
		<th align="left">Title</th>
		<th align="left" width="100">Created By</th>
		<th align="right" width="80">Created On</th>
	</tr>
	<?php
	while ($t = db_fetch($topics)) {?>
	<tr height="36">
		<td></td>
		<td><a href="topic.php?id=<?php echo $t["id"]?>"><?php echo $t["title"]?></a></td>
		<td><?php echo $t["first"]?> <?php echo $t["last"]?></td>
		<td align="right"><?php echo format_date($t["createdOn"])?></td>
	</tr>
	<?php }
	} else {
		echo drawEmptyResult("No Wiki Topics have been entered into the system yet.<br>Perhaps you would like to <a href='#bottom'>add one</a>?", 4);
	}?>
</table>

<a name="bottom"></a>

<?php if ($isAdmin && !$printing) {
	$form = new intranet_form;
	if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $user["id"], false, true);
	$form->addRow("itext",  "Title" , "title", "", "", true, 255);
	$form->addRow("select", "Type" , "typeID", "SELECT id, description FROM wiki_topics_types");
	$form->addCheckboxes("tags", "Tags", "wiki_tags", "wiki_topics_to_tags");
	$form->addRow("textarea", "Description" , "description", "", "", true);
	$form->addRow("submit"  , "post wiki topic");
	$form->draw("Add a Wiki Topic");
}

drawBottom(); ?>