<?php
include("../include.php");

drawTop();


?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Languages", 2, "add new organization", "organization_add_edit.php");?>
	<tr>
		<th align="left">Language</th>
		<th align="right">#</th>
	</tr>
	<?php
	$result = db_query("SELECT l.id, l.name, (SELECT count(*) FROM web_organizations_2_languages o2l WHERE o2l.languageID = l.id) as countlanguages FROM web_languages l ORDER by l.name");
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><?php echo $r["name"]?></td>
		<td align="right"><?php echo $r["countlanguages"]?></td>
	</tr>
	<?php }?>
</table>

<?php drawBottom();?>