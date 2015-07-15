<?php
include("../include.php");
drawTop();


if (isset($_GET["id"])) {
	$r = db_grab("SELECT 
			o.id,
			o.name, 
			o.address1, 
			o.address2,
			o.phone,
			o.hours,
			o.zip,
			o.lastUpdatedOn,
			ISNULL(u.nickname, u.firstname) + ' ' + u.lastname lastUpdatedBy
		FROM web_organizations o
		INNER JOIN intranet_users u ON o.lastUpdatedBy = u.userID
		WHERE o.id = " . $_GET["id"]);
} else {
	$_GET["id"] = 0;
}

?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow("Organization", 2, "edit", "organization_add_edit.php?id=" . $_GET["id"]);?>
	<form method="post" action="<?php echo $_josh["request"]["path_query"]?>">
	<tr>
		<td class="left">Name</td>
		<td><h1><?php echo $r["name"]?></h1></td>
	</tr>
	<tr>
		<td class="left">Address 1</td>
		<td><?php echo $r["address1"]?></td>
	</tr>
	<tr>
		<td class="left">Address 2</td>
		<td><?php echo $r["address2"]?></td>
	</tr>
	<tr>
		<td class="left">ZIP</td>
		<td><?php echo $r["zip"]?></td>
	</tr>
	<tr>
		<td class="left">Phone</td>
		<td><?php echo $r["phone"]?></td>
	</tr>
	<tr>
		<td class="left">Hours of Operation</td>
		<td><?php echo $r["hours"]?></td>
	</tr>
	<tr valign="top">
		<td class="left">Services</td>
		<td>
			<?php
			$services = db_query("SELECT s.name 
						FROM web_services s 
						INNER JOIN web_organizations_2_services o2s ON o2s.serviceID = s.id
						WHERE o2s.organizationID = {$_GET["id"]} ORDER BY s.name
						");
			while ($s = db_fetch($services)) {?>
				<?php echo $s["name"]?><br>
			<?php }?>
		</td>
	</tr>
	<tr valign="top">
		<td class="left">Languages (other than English)</td>
		<td>
			<?php
			$languages = db_query("SELECT l.name
						FROM web_languages l
						INNER JOIN web_organizations_2_languages o2l ON o2l.languageID = l.id
						WHERE o2l.organizationID = {$_GET["id"]} ORDER BY l.name");
			while ($l = db_fetch($languages)) {
				if ($l["name"] == "English") continue;?>
				<?php echo $l["name"]?><br>
			<?php }?>
		</td>
	</tr>
	<tr valign="top">
		<td class="left">Last Update</td>
		<td><?php echo format_date($r["lastUpdatedOn"])?> by <?php echo $r["lastUpdatedBy"]?></td>
	</tr>
	</form>
</table>
<?php drawBottom();?>