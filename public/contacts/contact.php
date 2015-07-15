<?php
include('../include.php');

if (url_action("delete")) {
	db_query("UPDATE intranet_objects SET
				isActive = 0,
				deletedOn = GETDATE(),
				deletedBy = {$user["id"]}
			WHERE id = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("undelete")) {
	db_query("UPDATE intranet_objects SET
				isActive = 1,
				deletedOn = NULL,
				deletedBy = NULL
			WHERE id = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("expunge")) {
	$result = db_query("SELECT id FROM intranet_instances WHERE objectID = " . $_GET["id"]);
	while ($r = db_fetch($result)) {
		db_query("delete from intranet_instances_to_tags where instanceID = " . $r["id"]);
		db_query("delete from intranet_instances_to_words where instanceID = " . $r["id"]);
		db_query("delete from intranet_instances where id = " . $r["id"]);
	}
	db_query("delete from intranet_objects where id = " . $_GET["id"]);
	url_change("./");
}

url_query_require();
drawTop();


$i = db_grab("SELECT
		i.id,
		(SELECT t1.tag FROM intranet_tags t1 INNER JOIN intranet_instances_to_tags i2t1 ON t1.id = i2t1.tagID WHERE t1.isActive = 1 AND t1.typeID = 10 AND i2t1.instanceID = o.instanceCurrentID) salutation,
		i.varchar_01 first,
		i.varchar_02 last,
		(SELECT t2.tag FROM intranet_tags t2 INNER JOIN intranet_instances_to_tags i2t2 ON t2.id = i2t2.tagID WHERE t2.isActive = 1 AND t2.typeID = 11 AND i2t2.instanceID = o.instanceCurrentID) suffix,
		i.varchar_03 nickname,
		i.varchar_04 org,
		i.varchar_05 title,
		i.varchar_06 address1,
		i.varchar_07 address2,
		RIGHT('00000' + RTRIM(i.numeric_01), 5) zip,
		i.varchar_08 phone,
		i.varchar_09 fax,
		i.varchar_10 cell,
		i.varchar_11 email,
		o.isActive,
		o.deletedOn,
		o.deletedBy,
		ISNULL(u.nickname, u.firstname) + ' ' + u.lastname deletedByName,
		z.city,
		z.state,
		i.text_01 notes
	FROM intranet_objects o
	INNER JOIN intranet_instances i ON i.id = o.instanceCurrentID
	LEFT  JOIN zip_codes z ON i.numeric_01 = z.zip
	LEFT  JOIN intranet_users     u ON u.userID = o.deletedBy
	WHERE o.id = " . $_GET["id"]);

if (!$i["id"]) {
	echo drawServerMessage("Either the link you clicked on is bad, or else this contact has been expunged from the system.  No further information is available.");
} else {
	if (!$i["isActive"]) echo drawServerMessage("This contact was deleted on " . format_date_excel($i["deletedOn"]) . " by <a href='/staff/view.php?id=" . $i["deletedBy"] . "'>" . $i["deletedByName"] . "</a>.  You can click below to undo the deletion.");
	?>
	<script language="javascript">
		<!--
		function confirmDelete(id) {
			if (confirm("Are you sure you want to delete this contact?")) location.href='<?php echo url_action_add("delete")?>';
		}
		
		function confirmExpunge(id) {
			if (confirm("Are you sure you want to expunge this contact?")) location.href='<?php echo url_action_add("expunge")?>';
		}
		//-->
	</script>
	<table class="left" cellspacing="1">
		<?php
		if ($isAdmin && $i["isActive"]) {
			echo drawHeaderRow("View Contact", 3, "edit", "contact_edit.php?id=" . $_GET["id"], "delete", "javascript:confirmDelete({$_GET["id"]});");
		} elseif ($isAdmin && !$i["isActive"]) {
			echo drawHeaderRow("View Contact", 3, "undelete", url_action_add("undelete"), "expunge", "javascript:confirmExpunge({$_GET["id"]});");
		} elseif ($i["isActive"]) {
			echo drawHeaderRow("View Contact", 3, "edit", "contact_edit.php?id=" . $_GET["id"], "delete", "javascript:confirmDelete({$_GET["id"]});");
		} else {
			echo drawHeaderRow("View Contact", 3, "undelete", url_action_add("undelete"));
		}?>
		<tr>
			<td class="left">Name</td>
			<td width="82%" colspan="2" class="input"><font size="+1"><b><?php if(!$i["isActive"]) {?><strike><font color="#666666"><?php }?><?php if($i["salutation"]) {?><?php echo $i["salutation"]?> <?php }?><?php echo $i["first"]?> <?php if($i["nickname"]) {?>(<?php echo $i["nickname"]?>)<?php }?> <?php echo $i["last"]?><?php if($i["suffix"]) {?>, <?php echo $i["suffix"]?><?php }?><?php if(!$i["isActive"]) {?></strike></font><?php }?></b></font></td>
		</tr>
		<?php if ($i["org"]) {?>
		<tr>
			<td class="left">Company</td>
			<td colspan="2" class="input" width="82%"><?php echo $i["org"]?></td>
		</tr>
		<?php }
		if ($i["title"]) {?>
		<tr>
			<td class="left">Job Title</td>
			<td colspan="2" class="input" width="82%"><?php echo $i["title"]?></td>
		</tr>
		<?php }?>
		<tr valign="top">
			<td class="left">Address</td>
			<td colspan="2" class="input" width="82%"><?php echo $i["address1"]?><br><?php if($i["address2"]) {?><?php echo $i["address2"]?><br><?php }?><?php echo $i["city"]?>, <?php echo $i["state"]?> <?php echo $i["zip"]?></td>
		</tr>
		<?php if ($i["phone"]) {?>
		<tr>
			<td class="left">Phone</td>
			<td colspan="2" class="input" width="82%"><?php echo $i["phone"]?></td>
		</tr>
		<?php }
		if ($i["fax"]) {?>
		<tr>
			<td class="left">Fax</td>
			<td colspan="2" class="input" width="82%"><?php echo $i["fax"]?></td>
		</tr>
		<?php }
		if ($i["cell"]) {?>
		<tr>
			<td class="left">Cell</td>
			<td colspan="2" class="input" width="82%"><?php echo $i["cell"]?></td>
		</tr>
		<?php }
		if ($i["email"]) {?>
		<tr>
			<td class="left">E-mail Address</td>
			<td colspan="2" class="input" width="82%"><a href="mailto:<?php echo $i["email"]?>"><?php echo $i["email"]?></a></td>
		</tr>
		<?php }
		if (strlen(trim($i["notes"]))) {?>
		<tr valign="top">
			<td class="left">Notes</td>
			<td colspan="2" class="input" width="82%"><?php echo nl2br($i["notes"])?></td>
		</tr>
		<?php }
		$found = false;
		$output = '<tr class="group"><td colspan="3">Tags</td></tr>';
		$tags = db_query("SELECT 
						f.tagTypeID,
						f.name,
						f.fieldTypeID
					FROM intranet_fields f
					JOIN intranet_tags_types t ON f.tagTypeID = t.id
					WHERE f.objectTypeID = 22 AND f.tagTypeID > 11 AND t.isActive = 1 ORDER BY f.precedence");
		while ($t = db_fetch($tags)) {
			$values = db_query("SELECT t.id, t.tag FROM intranet_tags t JOIN intranet_instances_to_tags i2t ON t.id = i2t.tagID WHERE t.isActive = 1 AND t.typeID = {$t["tagTypeID"]} AND i2t.instanceID = {$i["id"]} ORDER BY t.precedence");
			if (db_found($values)) {
				$found = true;
				$output .= '<tr valign="top"><td class="left">' . $t["name"] . '</td>';
				$output .= '<td class="input" width="82%" colspan="2">';
				while ($v = db_fetch($values)) $output .= '&nbsp;&#187;&nbsp;<a href="value.php?id=' . $v["id"] . '">' . $v["tag"] . '</a><br>';
				$output .= '</td></tr>';
			}
		}
		if ($found) echo $output;
		$output = "";
		?>
		<tr class="group">
			<td colspan="3">Object History</td>
		</tr>
		<tr>
			<th width="18%" align="left">Who</th>
			<th width="60%" align="left">What</th>
			<th width="22%" align="right">When</th>
		</tr>
		<?php
		$instances = db_query("SELECT
					i.id,
					o.instanceFirstID,
					o.instanceCurrentID,
					i.createdBy,
					ISNULL(u.nickname, u.firstname) + ' ' + u.lastname createdName,
					i.createdOn,
					i.varchar_01 firstname,
					i.varchar_02 lastname,
					i.varchar_03 nickname,
					i.varchar_04 organization,
					i.varchar_05 title,
					i.varchar_06 address1,
					i.varchar_07 address2,
					i.numeric_01 zip,
					i.varchar_08 phone,
					i.varchar_09 fax,
					i.varchar_10 cell,
					i.varchar_11 email,
					i.text_01 notes
				FROM intranet_instances i
				JOIN intranet_objects   o ON i.objectID = o.id
				JOIN intranet_users     u ON i.createdBy = u.userID
				WHERE o.id = {$_GET["id"]}
				ORDER BY i.createdOn ASC");
		while ($j = db_fetch($instances)) {
			if ($j["id"] == $j["instanceFirstID"]) {
				$description = "contact created";
				extract($j);
			} else {
				$description = "";
				$changes = array();
				if ($firstname    != $j["firstname"])    $changes[] = "firstname";
				if ($lastname     != $j["lastname"])     $changes[] = "lastname";
				if ($nickname     != $j["nickname"])     $changes[] = "nickname";
				if ($organization != $j["organization"]) $changes[] = "organization";
				if ($title        != $j["title"])        $changes[] = "title";
				if ($address1     != $j["address1"])     $changes[] = "address1";
				if ($address2     != $j["address2"])     $changes[] = "address2";
				if ($zip          != $j["zip"])          $changes[] = "zip";
				if ($phone        != $j["phone"])        $changes[] = "phone";
				if ($fax          != $j["fax"])          $changes[] = "fax";
				if ($cell         != $j["cell"])         $changes[] = "cell";
				if ($email        != $j["email"])        $changes[] = "email";
				if ($notes        != $j["notes"])        $changes[] = "notes";
				if (!count($changes)) {
					$description = "<i>no change</i>";
				} else {
					$description = join(", ", $changes) . " updated";
				}
				//$description = $nickname;
				$firstname    = $j["firstname"];
				$lastname     = $j["lastname"];
				$nickname     = $j["nickname"];
				$organization = $j["organization"];
				$title        = $j["title"];
				$address1     = $j["address1"];
				$address2     = $j["address2"];
				$zip          = $j["zip"];
				$phone        = $j["phone"];
				$fax          = $j["fax"];
				$cell         = $j["cell"];
				$email        = $j["email"];
				$notes        = $j["notes"];
			}
			$output = '<tr bgcolor="#FFFFFF" class="helptext"><td><a href="/staff/view.php?id=' . $j["createdBy"] . '">' . $j["createdName"] . '</a></td><td>' . $description . '</td><td align="right">' . format_date($j["createdOn"]) . '</td></tr>' . $output;
		}
		echo $output;
		echo "</table>";
	}
drawBottom();?>