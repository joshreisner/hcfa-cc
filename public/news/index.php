<?php
include("../include.php");

drawTop();

if (url_id()) {
	$r = db_grab("SELECT 
		n.headline,
		n.outlet,
		n.content,
		d2.extension docExt,
		d2.icon,
		d2.description docTypeDesc,
		n.image,
		d.extension imageExt,
		n.pubDate,
		n.url,
		n.description
		FROM news_stories n
		LEFT JOIN intranet_doctypes d ON n.imageTypeID = d.id
		LEFT JOIN intranet_doctypes d2 ON n.fileTypeID = d2.id
		WHERE n.id = " . $_GET["id"]);
	if ($r["image"]) {
		$filename = $locale . "news/thumbnail-" . $_GET["id"] . "." . $r["imageExt"];
		if (!file_exists($filename)) file_put($filename, $r["image"]);
	}
	echo drawTableStart();
	echo drawHeaderRow("News Item", 2, "edit", "edit.php?id=" . $_GET["id"]);?>
	<tr>
		<td class="left">Organization(s)</td>
		<td><?
		$organizations = db_query("SELECT 
			o.description 
			FROM news_stories_to_organizations ns2o
			JOIN organizations o ON ns2o.organizationID = o.id
			WHERE ns2o.newsID = " . $_GET["id"]);
		while ($o = db_fetch($organizations)) {
			echo $o["description"] . "<br>";
		}
		?></td>
	</tr>
	<tr>
		<td class="left">Headline</td>
		<td class="big"><?=draw_img($locale . "news/thumbnail-" . $_GET["id"] . "." . $r["imageExt"], false, "", "news-thumbnail")?><?=$r["headline"]?></td>
	</tr>
	<tr>
		<td class="left">News Outlet</td>
		<td><?=$r["outlet"]?></td>
	</tr>
	<tr>
		<td class="left">Date</td>
		<td><?=format_date($r["pubDate"])?></td>
	</tr>
	<tr>
		<td class="left">File</td>
		<td><table class="nospacing"><tr><td><?=draw_img($locale . $r["icon"], "download.php?id=" . $_GET["id"])?></td>
		<td><a href="download.php?id=<?=$_GET["id"]?>"> <?=$r["docTypeDesc"]?> (<?=format_size(strlen($r["content"]))?>)</a></td>
		</tr></table></td>
	</tr>
	<? if ($r["url"]) {?>
	<tr>
		<td class="left">URL</td>
		<td><a href="<?=$r["url"]?>"><?=$r["url"]?></a></td>
	</tr>
	<? }
	if ($r["description"]) {?>
	<tr>
		<td class="left">Description</td>
		<td class="text"><?=nl2br($r["description"])?></td>
	</tr>
	<? }
	echo drawTableEnd();

} else {
	echo drawTableStart();
	$colspan = ($isAdmin) ? 5 : 4;
	echo drawHeaderRow("", $colspan, "new", "#bottom");
	
	$result = db_query("SELECT 
			s.id,
			s.headline, 
			CASE WHEN ((SELECT COUNT(*) FROM news_stories_to_organizations n WHERE n.newsID = s.id) > 1) THEN (SELECT 'Multiple')
			ELSE (SELECT description FROM organizations o JOIN news_stories_to_organizations n ON o.id = n.organizationID WHERE n.newsID = s.id) END
			organization,
			s.outlet, 
			s.pubdate
		FROM news_stories s
		ORDER BY s.pubDate DESC");
	
	if (db_found($result)) {?>
		<tr>
			<th>Headline</th>
			<th>Outlet</th>
			<th>Organization</th>
			<th class="r">Date</th>
			<? if ($isAdmin) {?><th class="x"></th><? }?>
		</tr>
		<?
		while ($r = db_fetch($result)) {?>
		<tr>
			<td><a href="./?id=<?=$r["id"]?>"><?=format_string($r["headline"], 40)?></a></td>
			<td><?=$r["outlet"]?></td>
			<td><?=$r["organization"]?></td>
			<td class="r"><?=format_date($r["pubdate"], "n/a", "M d, Y", false)?></td>
			<?=deleteColumn("Delete news clip?", $r["id"])?>
		</tr>
		<? }
	} else {
		echo drawEmptyResult("No stories in the system yet");;
	}
	echo drawTableEnd();
	
	include("edit.php");
}


drawBottom();	
?>