<?php
include('../include.php');

if ($posting) {
	format_post_bits("isAdmin");
	$_POST["description"] = format_html($_POST["description"]);
	$id = db_enter("bulletin_board_topics", "title |description isAdmin");
	db_query("UPDATE bulletin_board_topics SET threadDate = GETDATE() WHERE id = " . $id);
	
	if ($_POST["isAdmin"] == "'1'") {
		//get topic 
		$r = db_grab("SELECT 
				t.title,
				t.description,
				u.userID,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				t.createdOn
				FROM bulletin_board_topics t
				JOIN intranet_users u ON t.createdBy = u.userID
				WHERE t.id = " . $id);
		
		//construct email
		$message  = drawEmailHeader();
		$message .= drawServerMessage("<b>Note</b>: This is an Administration/Human Resources topic from the <a href='http://" . $server . "/bulletin_board/'>Intranet Bulletin Board</a>.  For more information, please contact the <a href='mailto:hrpayroll@seedco.org'>Human Resources Department</a>.");
		$message .= '<table width="100%" cellpadding="3" cellspacing="1" border="0">';
		$message .= drawHeaderRow("Email", 2);
		$message .= drawThreadTop($r["title"], $r["description"], $r["userID"], $r["firstname"] . " " . $r["lastname"], $r["createdOn"]);
		$message .= '</table>' . drawEmailFooter();
		
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: " . $_josh["email_default"] . "\r\n";
		
		//get addresses & send
		$users = db_query("SELECT email FROM intranet_users WHERE isactive = 1");
		while ($u = db_fetch($users)) {
			mail($u["email"], $r["title"], $message, $headers);
		}
	}
	syndicateBulletinBoard();
	url_change();
}

drawTop();
drawSyndicateLink("bb");
?>
<table class="left" id="bb" cellspacing="1">
	<thead>
	<?php echo drawHeaderRow("All Topics", 4, "add new topic", "#bottom")?>
	<tr>
		<th align="left" width="320">Topic</td>
		<th align="left" width="120">Starter</td>
		<th>Replies</td>
		<th align="right">Last Post</td>
	</tr>
	</thead>
	<tbody>
	<?php echo drawBBPosts(false,
		drawEmptyResult("No topics have been added yet.  Why not <a href='#bottom'>be the first</a>?", 4)
	)?>
	</tbody>
</table>
<a name="bottom"></a>
<?php
if (!$printing) {
	$form = new intranet_form;
	if ($isAdmin) {
		$form->addUser("createdBy",  "Posted By" , $user["id"], false, true);
		$form->addCheckbox("isAdmin",  "Admin Post?", 0, "(check if yes)", true);
	}
	$form->addRow("itext",  "Subject" , "title", "", "", true);
	$form->addRow("textarea", "Message" , "description", "", "", true);
	$form->addRow("submit"  , "add new topic");
	$form->draw("Add a New Bulletin Board Topic");
}

drawBottom(); 
?>