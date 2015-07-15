<?	include("include.php");

$r = db_grab("SELECT
		u.userID,
		t.title,
		t.createdBy,
		t.description,
		t.timeSpent,
		t.ipAddress,
		t.ownerID,
		ISNULL(u.nickname, u.firstname) first,
		(SELECT COUNT(*) FROM helpdesk_tickets_attachments a WHERE a.ticketID = t.id) attachments,
		u.lastname last,
		u.officeID,
		o.name office,
		t.createdOn,
		t.statusID,
		t.typeID,
		t.departmentID,
		t.priorityID,
		p.isAdmin isAdminPriority,
		t.closedDate,
		y.description type,
		u.imageID,
		s.isActive isActiveOwner,
		ISNULL(s.nickname, s.firstname) ownerFirst,
		m.width,
		m.height,
		MONTH(t.createdOn) createdMonth,
		YEAR(t.createdOn) createdYear
	FROM helpdesk_tickets t
	JOIN intranet_users					u ON t.createdBy	= u.userID
	JOIN helpdesk_tickets_priorities	p ON t.priorityID	= p.id
	JOIN intranet_offices				o ON u.officeID		= o.id
	LEFT JOIN intranet_users			s ON t.ownerID		= s.userID
	LEFT JOIN helpdesk_tickets_types	y ON t.typeID		= y.id
	LEFT JOIN intranet_images			m ON u.imageID		= m.imageID
	WHERE t.id = " . $_GET["id"]);

//maybe ticketID is bad?
if (empty($r)) url_change("/helpdesk/");

if ($r["statusID"] != 9) { //open
	$typeRequired = false;
} elseif (!$r["typeID"]) { //closed, no type
	$typeRequired = false;
} else {
	$typeRequired = true;
}

//$isAdmin = ($isAdmin && ($r["departmentID"] == $user["departmentID"])) ? true : false;

if ($uploading) { //upload an attachment
	$type = getDocTypeID($_FILES["userfile"]["name"]);
	$content = format_binary(file_get_contents($_FILES["userfile"]["tmp_name"]));
	unlink($_FILES["userfile"]["tmp_name"]);
	db_query("INSERT INTO helpdesk_tickets_attachments (
		ticketID,
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
	//auto-assign ticket if unassigned and followup poster is an IT admin
	$followupAdmin = (isset($_POST["isAdmin"])) ? 1 : 0;
	if ($isAdmin && !$followupAdmin && empty($r["ownerID"])) {
		//set to it staff assigned if no status
		if ($r["statusID"] == 1) $r["statusID"] = 2;
		db_query("UPDATE helpdesk_tickets SET ownerID = {$user["id"]}, statusID = {$r["statusID"]}, updatedOn = GETDATE() WHERE id = " . $_GET["id"]);
	}
	
	//insert followup
	db_query("INSERT INTO helpdesk_tickets_followups (
				ticketID, 
				createdBy, 
				createdOn, 
				message,
				isAdmin
			) VALUES (
				{$_GET["id"]},
				{$user["id"]},
				GETDATE(),
				'{$_POST["message"]}',
				$followupAdmin
			)");
	
	//email and exit
	if ($followupAdmin) {
		emailITticket($_GET["id"], "followupadmin");
	} else {
		emailITticket($_GET["id"], "followup");
	}
	url_change();
}

drawTop();

echo drawServerMessage($helpdeskStatus, "center");

//populate dropdowns
$timeSpentOptions = array(0=>0, 15=>15, 30=>30, 45=>45, 60=>60, 75=>75, 90=>90, 105=>105, 120=>120, 135=>135, 150=>150);
if (!$r["timeSpent"]) $r["timeSpent"] = 0;
if (!isset($timeSpentOptions[$r["timeSpent"]])) {
	$timeSpentOptions[$r["timeSpent"]] = $r["timeSpent"];
	sort($timeSpentOptions);
}

if ($r["ownerID"] && !$r["isActiveOwner"]) {
	/* this is for if the ticket assignee has left before you are viewing this ticket
	interesting possibility would be to show all active owners for that time period */
	$ownerOptions[$r["ownerID"]] = $r["ownerFirst"];
	ksort($ownerOptions);
}

//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query("SELECT description, extension FROM intranet_doctypes ORDER BY description");
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t["extension"] . '")';
	$doctypes[] = " - " . $t["description"] . " (." . $t["extension"] . ")";
}

?>
<script language="javascript">
	<!--
	function validate(form) {
		tinyMCE.triggerSave();
		if (!form.message.value.length || (form.message.value == '<p>&nbsp;</p>')) return false;
		return true;
	}
	
	function validateAttachment(form) {
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
			if (<?=implode(" && ", $extensions)?>) {
				alert("Only these filetypes are supported by this system:\n\n <?=implode("\\n", $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filename.");
				return false;
			}
		}
		return true;
	}
	
	function newStatus(status) {
		if (status == 9) {
		<? if ($r["timeSpent"] && $r["typeID"]) {?>
			location.href='<?=$request["path_query"]?>&ticketID=<?=$_GET["id"]?>&newStatus=' + status;
		<? } else { ?>
			document.all["statusID<?=$r["statusID"]?>"].selected = true;
			alert("In order to close a ticket, you have to select a Type and an amount of Time Spent");
		<? } ?>
		} else {
			location.href='<?=$request["path_query"]?>&ticketID=<?=$_GET["id"]?>&newStatus=' + status;
		}
	}
	//-->
</script>

<table class="left" cellspacing="1">
	<?
	if ($r["statusID"] != 9) {
		$nextTicketID			= false;
		$lastTicketID			= false;
		$counter				= 0;
		$counterLastTicketID	= false;
		$counterCheckNext		= false;
		$tickets = db_query("SELECT id FROM helpdesk_tickets WHERE statusID <> 9 ORDER BY createdOn DESC");
		while ($t = db_fetch($tickets)) {
			$counter++;
			if ($_GET["id"] == $t["id"]) {
				$lastTicketID = $counterLastTicketID;
				$counterCheckNext = true;
				$ticketCount = $counter;
			} elseif ($counterCheckNext) {
				$nextTicketID = $t["id"];
				$counterCheckNext = false;
			} else {
				//echo $t["ticketID"] . " <> " . $_GET["id"] . "<br>";
			}
			$counterLastTicketID = $t["id"];
		}
		$title = "View Open Ticket (" . $ticketCount . " of " . $counter . ")";

		if ($isAdmin) {
			if ($lastTicketID && $nextTicketID) {
				echo drawHeaderRow($title, 2, "prev", "ticket.php?id=" . $lastTicketID, "next", "ticket.php?id=" . $nextTicketID);
			} elseif ($lastTicketID) {
				echo drawHeaderRow($title, 2, "prev", "ticket.php?id=" . $lastTicketID, "next", "");
			} elseif ($nextTicketID) {
				echo drawHeaderRow($title, 2, "prev", "", "next", "ticket.php?id=" . $nextTicketID);
			} else {
				echo drawHeaderRow($title, 2);
			}
		} else {
			echo drawHeaderRow($title, 2, "add a followup message","#bottom");
		}
	} else {
		echo drawHeaderRow("View Ticket", 2, "add a followup message","#bottom");
	}
	
	if ($isAdmin) {?>
	<form name="ticketForm">
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Status</td>
		<td><?=draw_form_select("statusID", "SELECT id, description FROM helpdesk_tickets_statuses", $r["statusID"], true, "", "newStatus(this.value);")?></td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Posted By</td>
		<td><?
		$sql = ($_josh["db"]["language"] == "mssql") ? "SELECT u.userID, u.lastname + ', ' + ISNULL(u.nickname, u.firstname) FROM intranet_users u WHERE u.isactive = 1 ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)" : "SELECT u.userID, CONCAT(u.lastname, ', ', IFNULL(u.nickname, u.firstname)) FROM intranet_users u WHERE u.isactive = 1 ORDER BY u.lastname, IFNULL(u.nickname, u.firstname)";
		echo draw_form_select("postedBy", $sql, $r["createdBy"], true, "", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newUser=' + this.value");
		?>
		<a href="user.php?id=<?=$r["createdBy"]?>">view all</a> / <a href="user.php?id=<?=$r["createdBy"]?>&month=<?=$r["createdMonth"]?>&year=<?=$r["createdYear"]?>">this month</a>
		</td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Location</td>
		<td><?=$r["office"]?>
		<a href="office.php?id=<?=$r["officeID"]?>">view all</a> / <a href="office.php?id=<?=$r["officeID"]?>&month=<?=$r["createdMonth"]?>&year=<?=$r["createdYear"]?>">this month</a>
		</td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Assigned To</td>
		<td>
			<?
			echo draw_form_select("ownerID", $ownerOptions, $r["ownerID"], false, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newOwner=' + this.value", false);
			if ($r["ownerID"]) {?>
			<a href="admin.php?id=<?=$r["ownerID"]?>">view all</a> / <a href="admin.php?id=<?=$r["ownerID"]?>&month=<?=$r["createdMonth"]?>&year=<?=$r["createdYear"]?>">this month</a>
			<? }?>
		</td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Time Spent</td>
		<td><?=draw_form_select("timeSpent", $timeSpentOptions, $r["timeSpent"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newTime=' + this.value", false);?> minutes</td>
	</tr>
	<? } elseif ($r["ownerID"]) {?>
	<tr height="30">
		<td class="left">Assigned To</td>
		<td><a href="/staff/view.php?id=<?=$r["ownerID"]?>"><?=$r["ownerFirst"]?></a></td>
	</tr>
	<? }?>
	<!-- <tr height="30">
		<td class="left">Ticket Number</td>
		<td><?=$_GET["id"]?></td>
	</tr> -->
	<tr height="30">
		<td class="left">Ticket Age</td>
		<td><?=format_time_business($r["createdOn"], $r["closedDate"]);?></td>
	</tr>
	<tr height="30">
		<td class="left">Type</td>
		<td><?=draw_form_select("typeID", "SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = " . $r["departmentID"] . " ORDER BY description", $r["typeID"], $typeRequired, false, "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newType=' + this.value");?>
			<? if ($isAdmin) {
			 if ($r["typeID"]) {
			 	echo '<a href="type.php?id=' . $r["typeID"] . '">view all</a> / <a href="type.php?id=' . $r["typeID"] . '&month=' . $r["createdMonth"] . '&year=' . $r["createdYear"] . '">this month</a>';
			 } else {
			 	echo '<a href="types.php">edit types</a>';
			 }
			 }?>
		</td>
	</tr>
	<tr height="30">
		<td class="left">Department</td>
		<td><?=draw_form_select("departmentID", "SELECT departmentID, shortName FROM intranet_departments WHERE isHelpdesk = 1", $r["departmentID"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newDepartment=' + this.value", false);?></td>
	</tr>
	<tr height="30">
		<td class="left">Priority</td>
		<td><?
		if ($isAdmin || $r["isAdminPriority"]) {
			echo draw_form_select("priorityID", "SELECT id, description FROM helpdesk_tickets_priorities", $r["priorityID"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newPriority=' + this.value");
		} else {
			echo draw_form_select("priorityID", "SELECT id, description FROM helpdesk_tickets_priorities WHERE isAdmin = 0", $r["priorityID"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newPriority=' + this.value");
		}
		?></td>
	</tr>
	<? if ($r["ipAddress"]) {?>
	<tr height="30">
		<td class="left">IP Address</td>
		<td><?=$r["ipAddress"]?></td>
	</tr>
	<? }
	
	if ($r["attachments"]) {?>
	<tr height="30">
		<td class="left">Attachment<? if ($r["attachments"] > 1) {?>s<? }?></td>
		<td>
			<table class="nospacing">
			<?
				$attachments = db_query("SELECT
				a.id,
				a.title,
				t.icon,
				t.description type
			FROM helpdesk_tickets_attachments a
			JOIN intranet_doctypes t ON a.typeID = t.id
			WHERE a.ticketID = " . $_GET["id"]);
		while ($a = db_fetch($attachments)) {?>
			<tr height="21">
				<td width="18"><a href="download.php?id=<?=$a["id"]?>"><img src="<?=$locale?><?=$a["icon"]?>" width="16" height="16" border="0"></a></td>
				<td><a href="download.php?id=<?=$a["id"]?>"><?=$a["title"]?></a></td>
			</tr>
		<? } ?>
		</table>
		</td>
	</tr>
	<? } ?>
	</form>
	<? 
$editurl = ($isAdmin) ? "ticket-edit.php?id=" . $_GET["id"] : false;
echo drawThreadTop($r["title"], $r["description"], $r["createdBy"], $r["first"] . " " . $r["last"], $r["imageID"], $r["width"], $r["height"], $r["createdOn"], $editurl);

$result = db_query("SELECT
					u.userID,
					f.message,
					ISNULL(u.nickname, u.firstname) first,
					u.lastname last,
					f.createdOn,
					u.imageID,
					m.width,
					m.height,
					f.isAdmin
				FROM helpdesk_tickets_followups	f
				JOIN intranet_users			u ON f.createdBy	= u.userID
				LEFT JOIN intranet_images	m ON u.imageID		= m.imageID
				WHERE f.ticketID = " . $_GET['id'] . "
				ORDER BY f.createdOn");
while ($r = db_fetch($result)) {
	echo drawThreadComment($r["message"], $r["userID"], $r["first"] . " " . $r["last"], $r["imageID"], $r["width"], $r["height"], $r["createdOn"], $r["isAdmin"]);
}

echo drawThreadCommentForm(true);

echo '</table>';

if (!$printing) {?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Attach Document", 2);?>
	<form enctype="multipart/form-data" action="<?=$request["path_query"]?>" method="post" onsubmit="javascript:return validateAttachment(this);">
	<tr>
		<td class="left">Document Name</td>
		<td><?=draw_form_text("title",  @$d["name"])?></td>
	</tr>
	<tr>
		<td class="left">File</td>
		<td><input type="file" name="userfile" size="40" class="field" value=""></td>
	</tr>
	<tr>
		<td class="bottom" align="center" colspan="2"><?=draw_form_submit("Attach Document");?></td>
	</tr>
	</form>
</table>
<? }
drawBottom(); ?>