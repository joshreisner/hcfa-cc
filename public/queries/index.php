<?php  include("../include.php");

if (url_action("delete")) {
	db_query("DELETE FROM queries WHERE id = " . $_GET["id"]);
	url_drop();
}

drawTop();
?>	
<table class="left" cellspacing="1">
	<?php if ($isAdmin) {
		echo drawHeaderRow("Reports", 6, "new", "query_edit.php");
	} else {
		echo drawHeaderRow("Reports", 5);
	}?>
	<tr>
		<th align="left" width="16"></th>
		<th align="left">Report Name</th>
		<th width="60">DLs</th>
		<th width="80">C/R</th>
		<th align="right">Updated</th>
		<?php if ($isAdmin) {?><th width="16"></th><?php }?>
	</tr>
	<?php 
	if ($_josh["db"]["language"] == "mssql") {
		$result = db_query("SELECT 
				q.id,
				q.name,
				q.description,
				ISNULL(q.updatedOn, q.createdOn) updatedOn,
				(SELECT count(*) FROM queries_executions e WHERE e.queryID = q.id) downloads,
				(SELECT TOP 1 num_columns FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC) num_columns,
				(SELECT TOP 1 num_rows    FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC) num_rows
			FROM queries q
			WHERE q.isActive = 1
			ORDER BY ISNULL(q.updatedOn, q.createdOn) DESC");
	} elseif ($_josh["db"]["language"] == "mysql") {
		$result = db_query("SELECT 
				q.id,
				q.name,
				q.description,
				ISNULL(q.updatedOn, q.createdOn) updatedOn,
				(SELECT count(*) FROM queries_executions e WHERE e.queryID = q.id) downloads,
				(SELECT num_columns FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC LIMIT 1) num_columns,
				(SELECT num_rows    FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC LIMIT 1) num_rows
			FROM queries q
			WHERE q.isActive = 1
			ORDER BY ISNULL(q.updatedOn, q.createdOn) DESC");
	}
	while ($r = db_fetch($result)) {?>
	<tr height="46">
		<td><a href="download.php?id=<?php echo $r["id"]?>"><img src="<?php echo $locale?>images/doctypes/xls.png" width="16" height="16" border="0"></a></td>
		<td><a href="download.php?id=<?php echo $r["id"]?>"><b><?php echo $r["name"]?></b></a><?php if($isAdmin){?>&nbsp;&nbsp;/&nbsp;<a href="query_edit.php?id=<?php echo $r["id"]?>">edit</a><?php }?><br><?php echo $r["description"]?></td>
		<td align="center"><?php echo number_format($r["downloads"])?></td>
		<td align="center"><nobr><?php echo number_format($r["num_columns"])?> / <?php echo number_format($r["num_rows"])?></nobr></td>
		<td align="right"><nobr><?php echo format_date($r["updatedOn"])?></nobr></td>
		<?php echo deleteColumn("Delete this database query?", $r["id"])?>
	</tr>
	<?php }?>
</table>
<?php drawBottom() ?>