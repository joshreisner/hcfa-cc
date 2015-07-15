<?php include("include.php");
drawTop();
?>

<table class="left" cellspacing="1">
	<?php
	echo drawHeaderRow("Assign " . db_grab("SELECT shortName name FROM intranet_departments WHERE departmentID = " . $departmentID) . " Tickets", 5);
	echo drawTicketHeader();

	//unassigned tickets
	$result = db_query("select
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.ticketID = t.id) as ticketfollowups,
				t.createdBy,
				t.updatedOn,
				t.departmentID,
				t.id,
				t.ownerID,
				t.priorityID,
				t.createdOn,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM administrators a WHERE a.moduleID = 3 AND a.userID = t.createdBy) AS isAdminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN intranet_users			u ON u.userID	= t.createdBy
			LEFT JOIN intranet_images	m ON u.imageID	= m.imageID
			WHERE (t.statusID <> 9 OR t.statusID IS NULL) AND (t.ownerID IS NULL OR t.ownerID = 0) AND t.departmentID = $departmentID
			ORDER BY t.priorityID");
	if (db_found($result)) {?>
		<tr class="group">
			<td colspan="5">Unassigned Tickets</td>
		</tr>
		<?php while ($r = db_fetch($result)) echo drawTicketRow($r);
	}
	
	//your tickets
	$result = db_query("SELECT
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.id = t.id) as ticketfollowups,
				t.createdBy,
				t.updatedOn,
				t.departmentID,
				t.id,
				t.ownerID,
				t.priorityID,
				t.createdOn,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM administrators a where a.moduleID = 3 and a.userID = t.createdBy) as isAdminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN intranet_users   u ON u.userID    = t.createdBy
			LEFT  JOIN intranet_images  m ON u.imageID   = m.imageID
			WHERE (t.statusID <> 9 OR t.statusID IS NULL) AND t.ownerID = " . $user["id"] . " AND t.departmentID = $departmentID
			ORDER BY t.priorityID");
	if (db_found($result)) {?>
		<tr class="group">
			<td colspan="5">Your Tickets</td>
		</tr>
		<?php while ($r = db_fetch($result)) echo drawTicketRow($r);
	}
	
	//other tickets
	$result = db_query("select
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.id = t.id) as ticketfollowups,
				t.createdBy,
				t.updatedOn,
				t.departmentID,
				t.id,
				t.ownerID,
				t.priorityID,
				t.createdOn,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM administrators a WHERE a.moduleID = 3 AND a.userID = t.createdBy) AS isAdminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN intranet_users   u ON u.userID    = t.createdBy
			LEFT  JOIN intranet_images  m ON u.imageID   = m.imageID
			WHERE (t.statusID <> 9 OR t.statusID IS NULL) AND t.ownerID <> 0 AND t.ownerID <> " . $user["id"] . " AND t.departmentID = $departmentID
			ORDER BY t.priorityID");
	if (db_found($result)) {?>
		<tr class="group">
			<td colspan="5">Other People's Tickets</td>
		</tr>
		<?php
		while ($r = db_fetch($result)) echo drawTicketRow($r);
	}?>
</table>
<?php drawBottom(); ?>