<?php
//joshlib & localize
	date_default_timezone_set('America/New_York');
	$_josh["styles"]			= array("field"=>"field", "checkbox"=>"checkbox", "select"=>"select", "button"=>"button", "textarea"=>"mceEditor");
	$_josh["basedblanguage"]	= "mssql";
	@extract(includeLibrary()) or die("Can't locate library! " . $_SERVER["DOCUMENT_ROOT"]);
	$locale = "/_hcfa-cc/";

//determine location & scenario
	//get page info
	$page = getPage();
	if (!isset($page["id"])) { //if page doesn't exist, create it
		error_debug("creating page");
		db_query("INSERT INTO pages ( url, name, isSecure ) VALUES ( '{$_josh["request"]["path"]}', 'Untitled Page', 1 )");
		$page = getPage();
	}
	$location	= $_josh["request"]["folder"];
	$uploading	= (isset($_FILES["userfile"]["tmp_name"]) && !empty($_FILES["userfile"]["tmp_name"])) ? true : false;
	//$uploading	= (isset($_FILES["userfile"]["tmp_name"]) && file_exists($_FILES["userfile"]["tmp_name"])) ? true : false;
	$printing	= (isset($_GET["print"])) ? true : false;
	$editing	= (isset($_GET["id"])) ? true : false;
	$action		= (isset($_GET["action"])) ? $_GET["action"] : false;
	
//query user & module info
	if (!isset($_COOKIE["last_login"]) || empty($_COOKIE["last_login"])) {
		//not logged in
		if ($_josh["request"]["path"] != "/") {
			if (isset($_GET["goto"])) {
				url_change("/index.php?goto=" . $_GET["goto"]);
			} elseif ($page["isSecure"]) {
				url_change("/index.php?goto=" . urlencode($_josh["request"]["path_query"]));
			}
		}
	} else {
		//get user info
		$user = db_grab("SELECT 
			u.userID id,
			u.firstname first_name,
			u.lastname last_name,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.email,
			" . db_pwdcompare("", "u.password") . " password,
			" . db_datediff("u.updatedOn", "GETDATE()") . " update_days,
			p.url homepage,
			u.rankID,
			u.departmentID,
			d.isHelpdesk,
			u.isOpenHelp,
			u.isOpenTools,   
			u.isOpenAreas,
			u.isOpenCalendar,
			u.isOpenContacts,
			u.isOpenBulletinBoard,
			u.isOpenHelpdesk,
			u.isOpenDocuments,
			u.isOpenStaff,
			u.isActive,
			u.updatedOn
		FROM intranet_users u
		JOIN intranet_departments d ON u.departmentID = d.departmentID
		JOIN pages p				ON u.homePageID = p.id
		WHERE email = '{$_COOKIE["last_login"]}' AND u.isActive = 1");
	
		//user isn't active or has bad cookie
		if (!isset($user["id"]) || !$user["id"]) url_change("/index.php?goto=" . urlencode($_josh["request"]["path_query"]));
		$user["full_name"]		= $user["first"] . " " . $user["last"];

		//get modules info
		$result = db_query("SELECT 
				m.id,
				p.url,
				m.name,
				m.pallet,
				m.isPublic,
				(SELECT COUNT(*) FROM administrators a WHERE a.userID = {$user["id"]} AND a.moduleID = m.id) isAdmin
			FROM modules m
			JOIN pages p ON p.id = m.homePageID
			WHERE m.isActive = 1
			ORDER BY m.precedence");
	
		$modules	= array();
		$areas		= array();
		$user["isAdmin"] = false;
		while ($r = db_fetch($result)) {
			$modules[$r["id"]] = array(
				"id"		=> $r["id"],
				"name"		=> $r["name"],
				"url"		=> $r["url"],
				"isPublic"	=> $r["isPublic"],
				"pallet"	=> $r["pallet"],
				"isAdmin"	=> $r["isAdmin"]
			);
			if (!$r["pallet"]) $areas[$r["name"]] = $r["id"];
			if (($r["name"] == "Admin") && $r["isAdmin"]) $user["isAdmin"] = true;
		}
		ksort($areas);
	
		//generic variable to indicate admin privileges for the current module
		$isAdmin = (isset($modules[$page["moduleID"]])) ? $modules[$page["moduleID"]]["isAdmin"] : false;
	
		//check to see if user needs update
		if (($user["update_days"] > 90 || empty($user["updatedOn"])) && $page["isSecure"] && ($_josh["request"]["path"] != "/staff/add_edit.php")) {
			error_debug("user needs address update");
			url_change("/staff/add_edit.php?id=" . $user["id"]);
		} elseif ($user["password"] && $page["isSecure"]) {
			error_debug("user needs password update");
			url_change("/login/password_update.php");
		}
	}
	

//special pages that don't belong to a module still need info

	if (!isset($page["moduleID"])) $page["moduleID"] = 0;
	if (!isset($modules[$page["moduleID"]])) {
		error_debug("unspecified module");
		$modules[$page["moduleID"]]["pallet"]	= false;
		$modules[$page["moduleID"]]["isPublic"]	= false;
		$modules[$page["moduleID"]]["pallet"]	= false;
		$modules[$page["moduleID"]]["name"]		= "Intranet";
		$modules[$page["moduleID"]]["isAdmin"]	= false;
	}

//handle switch updates
	//side menu pref
	if (isset($_GET["toggleMenuPref"])) {
		db_query("UPDATE intranet_users SET " . $_GET["toggleMenuPref"] . " = " . abs($user[$_GET["toggleMenuPref"]] - 1) . " WHERE userID = " . $user["id"] . ";");
		url_query_drop("toggleMenuPref");
	}

//done!
error_debug("done processing include!");
	
//custom functions - miscellaneous
	function includeLibrary() {
		global $_josh;
		$possibilities = array(
			"/home/hcfacc/www/joshlib/index.php", //production
			"/home/forge/hcfa-cc.joshreisner.com/joshlib/index.php", //staging
			"/Users/joshreisner/Sites/hcfa-cc/joshlib/index.php", //local
		);
		foreach ($possibilities as $p) if (@include($p)) return $_josh;
		return false;
	}

	function email_invite($id, $email, $name) {
		global $_josh, $user;
		$email = format_email($email);
		$message = '<tr><td class="text">
			Welcome ' . $name . '!  You can
			<a href="http://' . $_josh["request"]["host"] . '/login/password_reset.php?id=' . $id . '">log in to the Intranet now</a>.  
			The system will prompt you to pick a password and update your contact information.
			<br><br>
			If you run into problems, please ask <a href="mailto:' . $user["email"] . '">' . $user["full_name"] . '</a> for help.
			</td></tr>';
		email_user($email, "Intranet Login Information", $message);
	}

	function email_user($address, $title, $content, $colspan=1) {
		global $_josh;
		
		$message = drawEmailHeader() . 
			drawTableStart() . 
			drawHeaderRow($title, $colspan) . 
			$content . 
			drawTableEnd() . 
			drawEmailFooter();
	
		$headers	 = "MIME-Version: 1.0\r\n";
		$headers	.= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers	.= "From: " . $_josh["email_default"] . "\r\n";
		if (!mail($address, $title, $message, $headers)) error_handle("Couldn't Send Email", "The message to " . $address . " was rejected by the mailserver for some reason", true);
	}
	
	function error_email($msg="Undefined error message") {
		global $user, $_josh;
		//if (isset($_josh["email_default"]) && ($user["id"] != 1)) {
		if (isset($_josh["email_default"]) && isset($_josh["email_admin"])) {
			if (isset($user["id"])) {
				if ($_josh["email_admin"] == $user["email"]) return;
				$msg = str_replace("<!--user-->", "<a href='http://" . $_josh["request"]["host"] . "/users/view.php?id=" . $user["id"] . "'>" . $user["full_name"] . "</a>", $msg);
			} else {
				$msg = str_replace("<!--user-->", "<i>User ID not set yet</i>", $msg);
			}
			email($_josh["email_admin"], $msg, "Error: " . $_josh["request"]["host"], $_josh["email_default"]);
		}
	}
	
    function login($username, $password, $skippass=false) {
    	global $user;
        if ($skippass) {
			error_debug("<b>login</b> running without password");
	        $user = db_grab("SELECT u.userID, p.url FROM intranet_users u JOIN pages p ON u.homePageID = p.id WHERE u.email = '$username'");
        } else {
			error_debug("<b>login</b> running with password");
   	        $user = db_grab("SELECT u.userID, p.url FROM intranet_users u JOIN pages p ON u.homePageID = p.id WHERE u.email = '$username' AND " . db_pwdcompare($password, "u.password") . " = 1");
        }
        if ($user["userID"]) {
            db_query("UPDATE intranet_users SET lastlogin = GETDATE() WHERE userID = " . $user["userID"]);
            return true;
		}
		return false;
	}
	
	function getPage() {
		global $_josh;
		return db_grab("SELECT p.id, p.name, p.helpText, p.isAdmin, p.isSecure, m.id moduleID, m.name module FROM pages p
		LEFT JOIN modules m ON p.moduleID = m.id WHERE p.url = '{$_josh["request"]["path"]}'");
	}
	
//post functions
	function getDocTypeID($filename) {
		$array = explode(".", $filename);
		return db_grab("SELECT id FROM intranet_doctypes WHERE extension = '" . array_pop($array) . "'");
	}

	function updateInstanceWords($id, $text) {
		global $ignored_words;
		$words = array_diff(split("[^[:alpha:]]+", strtolower(strip_tags($text))), $ignored_words);
		if (count($words)) {
			$text = implode("|", $words);
			db_query("index_intranet_instance $id, '$text'");
		}
	}

//delete functions
	function deleteLink($prompt=false, $id=false, $action="delete", $index="id") {
		global $_GET;
		if (!$id && isset($_GET["id"])) $id = $_GET[$index];
		$prompt = ($prompt) ? "'" . str_replace("'", '"', $prompt) . "'" : "false";
		return "javascript:promptRedirect('" . url_query_add(array("action"=>$action, $index=>$id), false) . "', " . $prompt . ");";
	}
	
	function deleteColumn($prompt=false, $id=false, $action="delete", $adminOnly=true) {
		global $isAdmin, $printing, $locale;
		if ($printing || ($adminOnly && !$isAdmin)) return false;
		return '<td class="delete"><a href="' . deleteLink($prompt, $id, $action) . '"><i class="glyphicon glyphicon-remove"></i></a></td>';
	}

	function drawCheckboxText($chkname, $description) {
		return '<span class="clickme" onclick="javascript:toggleCheckbox(\'' . $chkname . '\');">' . $description . '</span>';
	}

//rss functions (syndication)
	function drawSyndicateLink($name) {
		global $locale;
		return '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . $locale . 'syndicate/' . $name . '.xml">';
	}
	
	function syndicateBulletinBoard() {
		global $_josh, $locale;
		
		$items = array();
		
		$topics = db_query("SELECT 
				t.id,
				t.title,
				t.description,
				t.isAdmin,
				t.threadDate,
				(SELECT COUNT(*) FROM bulletin_board_followups f WHERE t.id = f.topicID AND f.isActive = 1) replies,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				u.email
			FROM bulletin_board_topics t
			JOIN intranet_users u ON u.userID = t.createdBy
			WHERE t.isActive = 1 
			ORDER BY t.threadDate DESC", 15);
		
		while ($t = db_fetch($topics)) {
			if ($t["isAdmin"]) $t["title"] = "ADMIN: " . $t["title"];
			if ($t["replies"] == 1) {
				$t["title"] .= " (" . $t["replies"] . " comment)";
			} elseif ($t["replies"] > 1) {
				$t["title"] .= " (" . $t["replies"] . " comments)";
			}
			$items[] = array(
				"title" => $t["title"],
				"description" => $t["description"],
				"link" => "http://intranet.seedco.org/bb/topic.php?id=" . $t["id"],
				"date" => $t["threadDate"],
				"author" => $t["email"] . " (" . $t["firstname"] . " " . $t["lastname"] . ")"
			);
		}

		file_rss("Bulletin Board: Last 15 Topics", "http://" . $_josh["request"]["host"] . "/bb/", $items, $locale . "syndicate/bb.xml");
	}
	

//form class
	class intranet_form {
		var $rows, $js;
		
		function addUser($name="userID", $desc="User", $default=0, $nullable=false, $admin=false) {
			global $rows, $location;
			$rows .= '<tr>
				<td class="left">' . $desc . '</td>
				<td';
			if ($admin) $rows .= ' class="admin ' . $location . '-hilite"';
			$rows .= '>' . drawSelectUser($name, $default, $nullable) . '</td>
			</tr>';
		}
		
		function addCheckbox($name="", $desc="", $default=0, $additionalText="(check if yes)", $admin=false) {
			global $rows, $location;
			$rows .= '<tr>
				<td class="left">' . $desc . '</td>
				<td';
			if ($admin) $rows .= ' class="admin ' . $location . '-hilite"';
			$rows .= '><table class="nospacing">
						<tr>
							<td class="checkbox-spacing">' . draw_form_checkbox($name, $default) . '</td>
							<td>' . drawCheckboxText($name, $additionalText) . '</td>
						</tr>
					</table>
				</td>
			</tr>';
		}
		
		function addCheckboxes($name, $desc, $table, $linking_table=false, $table_col=false, $link_col=false, $id=false, $admin=false) {
			global $rows;
			$rows .= '<tr';
			if ($admin) $rows .= ' class="admin"';
			
			//special exceptions (this is terrible)
			if ($table == "modules") {
				$description = "name";
			} elseif ($table == 'skills') {
				$description = 'title';
			} else {
				$description = "description";
			}
			
			$rows .= '>
				<td class="left">' . $desc . '</td>
				<td>';
				if ($id) {
					$result = db_query("SELECT 
							t.id, 
							t.$description description, 
							(SELECT COUNT(*) FROM $linking_table l WHERE l.$table_col = $id AND l.$link_col = t.id) checked
						FROM $table t
						WHERE t.isActive = 1
						ORDER BY t.$description");
				} else {
					$result = db_query("SELECT id, $description description, 0 checked FROM $table WHERE isActive = 1 ORDER BY $description");
				}
				if ($total = db_found($result)) {
					$counter = 0;
					$max = ceil($total / 3);
					$rows .= '<table class="nospacing" width="100%"><tr>';
					while ($r = db_fetch($result)) {
						if ($counter == 0) $rows .= '<td width="33%" style="vertical-align:top;"><table class="nospacing">';
						$chkname = "chk_" . $name . "_" . $r["id"];
						$rows .= '
							<tr>
							<td>' . draw_form_checkbox($chkname, $r["checked"]) . '</td>
							<td>' . drawCheckboxText($chkname, $r["description"]) . '</td>
							</tr>';
						if ($counter == ($max - 1)) {
							$rows .= '</table></td>';
							$counter = 0;
						} else {
							$counter++;
						}
					}
					if ($counter != 0) $rows .= '</table></td>';
					$rows .= '</tr></table>';
				}
				$rows .= '
				</td>
			</tr>';
		}
		
		function addSelect($name="", $desc="", $sql="", $default=0, $nullable=false, $bgcolor=false) {
			global $rows;
			$rows .= '
			<tr>
				<td>' . $desc . '</td>
				<td>' . draw_form_select($name, $sql, $default, $nullable) . '</td>
			</tr>';
		}
		
		function addJavascript($conditions, $message) {
			global $js;
			$js .= "
				if (" . $conditions . ") errors[errors.length] = '" . addslashes($message) . "';
			";
		}
		
		function addRaw($row) {
			global $rows;
			$rows .= $row;
		}
		
		function addGroup($text="") {
			global $rows;
			$rows .= '
				<tr class="group">
					<td colspan="2">' . $text . '</td>
				</tr>';
		}
			
		function addRow($type, $title, $name="", $value="", $default="", $required=false, $maxlength=50, $onchange=false) {
			global $rows, $js, $months, $month, $today, $year, $_josh;
			$textlength = ($maxlength > 50) ? 50 : $maxlength;
			$value = trim($value);
			if ($type == "raw") {
				$rows .= $title;
			} else {
				$rows .= '<tr>';
				if (($type != "button") && ($type != "submit") && ($type != "hidden") && ($type != "raw")) $rows .= '<td class="left">' . $title . '</td>';
				
				if ($type == "text") { //output text, no form element
					$rows .= '<td>' . $value . '</td>';
				} elseif ($type == "date") {
					$rows .= '<td>' . draw_form_date($name, $value, false, false, $required) . '</td>';
				} elseif ($type == "datetime") {
					$rows .= '<td>' . draw_form_date($name, $value, true) . '</td>';
				} elseif ($type == "checkbox") {
					$rows .= '<td>' . draw_form_checkbox($name, $value) . '</td>';
				} elseif ($type == "itext") {
					$rows .= '<td>' . draw_form_text($name, $value, false, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "phone") {
					$rows .= '<td>' . draw_form_text($name, $value, 14, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "extension") {
					$rows .= '<td>' . draw_form_text($name, $value, 4, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "password") {
					$rows .= '<td>' . draw_form_password($name, $value, $textlength, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "select") {
					$rows .= '<td>';
					$rows .= draw_form_select($name, $value, $default, $required, false, $onchange);
					$rows .= '</td>';
				} elseif ($type == "user") {
					$result = db_query("SELECT 
											userID, 
											ISNULL(nickname, firstname) first,
											lastname last 
										FROM intranet_users
										WHERE isActive = 1
										ORDER by lastname");
					while ($r = db_fetch($result)) {
						$options[$r["userID"]] = $r["first"] . ", " . $r["last"];
					}
					$rows .= '<td>';
					$rows .= draw_form_select($name, $options, $default, $required, false, $onchange);
					$rows .= '</td>';
				} elseif ($type == "department") {
					$rows .= '<td><select name="' . $name . '">';
					$result = db_query("SELECT 
											departmentID,
											departmentName,
											quoteLevel
										FROM intranet_departments
										WHERE isActive = 1
										ORDER by precedence");
					while ($r = db_fetch($result)) {
						$rows .= '<option value="' . $r["departmentID"] . '"';
						if ($r["departmentID"] == $default) $rows .= ' selected';
						$rows .= '>';
						if ($r["quoteLevel"] == 2) {
							$rows .= "&nbsp;&#183;&nbsp;";
						} elseif ($r["quoteLevel"] == 3) {
							$rows .= "&nbsp;&nbsp;&nbsp;-&nbsp;";
						}
						$rows .= $r["departmentName"] . '</option>';
					}
					$rows .= '</select></td>';
				} elseif ($type == "userpic") {
					$rows .= '<td>' . drawName($name, $value, $default, true, " ") . '</td>';
				} elseif ($type == "textarea") {
					$rows .= '<td>' . draw_form_textarea($name, $value) . '</td>';
					$js .= " tinyMCE.triggerSave();" .  $_josh["newline"];
					if ($required) $js .= "if (!form." . $name . ".value.length || (form." . $name . ".value == '<p>&nbsp;</p>')) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "textarea-plain") {
					$rows .= '<td>' . draw_form_textarea($name, $value, "noMceEditor") . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "hidden") {
					$rows .= draw_form_hidden($name, $value);
				} elseif ($type == "submit") {
					$rows .= '<td colspan="2" align="center" class="bottom">' . draw_form_submit($title, "button") . '</td>';
				} elseif ($type == "button") {
					$rows .= '<td colspan="2" align="center" class="bottom">' . draw_form_button($title, $value, "button") . '</td>';
				} elseif ($type == "file") {
					$rows .= '<td>' . draw_form_file($name) . '</td>';
				}
				$rows .= '</tr>' . $_josh["newline"];
			}
		}	
		
		function draw($pageTitle) {
			global $rows, $_josh, $js, $location, $printing;
			if ($printing) return;
			if ($js) {
			?>
			<script language="javascript">
			<!--
				function validate(form) {
					var errors = new Array();
					<?php echo $js;?>
					return showErrors(errors);
				}
			//-->
			</script>
			<?php }?>
			<a name="bottom"></a>
			<table class="left" cellspacing="1">
				<tr>
					<td class="head <?php echo $location?>" colspan="2"><?php echo $pageTitle?></td>
				</tr>
				<form method="post" action="<?php echo $_josh["request"]["path_query"]?>" enctype="multipart/form-data" onsubmit="javascript:return validate(this);">
				<?php echo $rows;?>
				</form>
			</table>
			<?php
		}
	}

	function htmlwrap($str, $len=60) {
		$words = explode(" ", strip_tags($str));
		foreach ($words as $word) {
		  if (strlen($word) > $len) {
			  $parts = explode($word, $str);
			  if (count($parts) == 3) $str = $parts[0] . $word . $parts[1] . substr($word, 0, $len-3) . "..." . $parts[2];
		  }
		}
		return $str;
	}

	function db_enter($table, $fields, $index="id") {
		global $editing, $language, $user;
		
		$fields = explode(" ", $fields);
		foreach ($fields as $field) {
			if ($field == "password") { //binary password
				if ($editing) {
					$query1[] = $field . " = PWDENCRYPT('" . $_POST[$field] . "')";
				} else {
					$query1[] = $field;
					$query2[] = $field . " = PWDENCRYPT('" . $_POST[$field] . "')";
				}
			} elseif (substr($field, 0, 1) == "#") { //numeric
				$field = substr($field, 1);
				if (empty($_POST[$field])) $_POST[$field] = "NULL";
				if ($editing) {
					$query1[] = $field . " = " . $_POST[$field];
				} else {
					$query1[] = $field;
					$query2[] = $_POST[$field];
				}
			} elseif (substr($field, 0, 1) == "*") { //date
				$field = substr($field, 1);
				if (isset($_POST["no" . $field])) {
					if ($editing) {
						$query1[] = $field . " = NULL";
					} else {
						$query1[] = $field;
						$query2[] = "NULL";
					}
				} else {
					if ($editing) {
						$query1[] = $field . " = " . format_post_date($field);
					} else {
						$query1[] = $field;
						$query2[] = format_post_date($field);
					}
				}
			} elseif (substr($field, 0, 1) == "@") { //file
				$field = substr($field, 1);
				if (isset($_POST[$field])) { //file posting is optional, from a php point of view
					if ($editing) {
						$query1[] = $field . " = " . format_binary($_POST[$field]);
					} else {
						$query1[] = $field;
						$query2[] = format_binary($_POST[$field]);
					}
				}
			} elseif (substr($field, 0, 1) == "|") { //html
				$field = substr($field, 1);
				if (isset($_POST[$field])) {
					if ($editing) {
						$query1[] = $field . " = " . format_html($_POST[$field]);
					} else {
						$query1[] = $field;
						$query2[] = "'" . format_html($_POST[$field]) . "'";
					}
				}
			} else { //text
				$_POST[$field] = trim($_POST[$field]);
				$_POST[$field] = (empty($_POST[$field])) ? "NULL" : "'" . $_POST[$field] . "'";
				if ($editing) {
					$query1[] = $table . '.' . $field . " = " . $_POST[$field];
				} else {
					$query1[] = $table . '.' . $field;
					$query2[] = $_POST[$field];
				}
			}
		}
		if ($editing) {
			$query1[] = "updatedOn = GETDATE()";
			if (isset($_POST["updatedBy"])) {
				$query1[] = "updatedBy = " . $_POST["updatedBy"];
			} else {
				$query1[] = "updatedBy = " . $user["id"];
			}
			db_query("UPDATE " . $table . " SET " . implode(", ", $query1) . " WHERE " . $index . " = " . $_GET["id"]);
			return $_GET["id"];
		} else {
			$query1[] = "createdOn";
			$query2[] = "GETDATE()";
			$query1[] = "createdBy";
			$query2[] = (isset($_POST["createdBy"])) ? $_POST["createdBy"] : $user["id"];
			$query1[] = "isActive";
			$query2[] = 1;
			$r = db_query("INSERT INTO " . $table . " ( " . implode(", ", $query1) . " ) VALUES ( " . implode(", ", $query2) . ")");
			return $r;
		}
	}
	
	function arrayRemove($needle, $haystack) {
		$return = array();
		foreach ($haystack as $value) if ($value != $needle) $return[] = $value;
		return $return;
	}
	
//custom functions - draw functions

function drawBBPosts($count=15, $error='') {
	if ($topics = db_table("SELECT 
			t.id,
			t.title,
			t.isAdmin,
			t.threadDate,
			(SELECT COUNT(*) FROM bulletin_board_followups f WHERE t.id = f.topicID AND f.isActive = 1) replies,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname
		FROM bulletin_board_topics t
		JOIN intranet_users u ON u.userID = t.createdBy
		WHERE t.isActive = 1 AND (t.temporary IS NULL OR t.temporary = 0 OR 
			(t.temporary = 1 AND DATEDIFF(NOW(), t.createdOn) < 31))
		ORDER BY t.threadDate DESC", $count)) {

		foreach ($topics as &$topic) {
			if ($topic["isAdmin"]) $topic["replies"] = "-";
			$topic = '
			<tr class="thread' . ($topic["isAdmin"] ? ' admin' : '')  . '">
				<td class="input"><a href="topic.php?id=' . $topic["id"] . '">' . $topic["title"] . '</a></td>
				<td>' . $topic["firstname"] . ' ' . $topic["lastname"] . '</td>
				<td align="center">' . $topic["replies"] . '</td>
				<td align="right">' . format_date($topic["threadDate"]) . '</td>
			</tr>';
		}
		
		return implode($topics);
	} else {
		return $error;
	}	
}
	function drawTableStart() {
		return '<table cellspacing="1" class="left">';
	}
	
	function drawTableEnd() {
		return '</table>';
	}
	
	function drawEmptyResult($text="None found.", $colspan=1) {
		return '<tr><td class="empty" colspan="' . $colspan . '">' . $text . '</td></tr>';
	}
	
	function drawServerMessage($str, $align="left") {
		if (empty($str) || !format_html_text($str)) return false;
		return '<div class="alert alert-warning" align="' . $align . '">' . $str . '</div>';
	}
							
	function drawNavigation() {
		global $user, $isAdmin, $page, $printing, $location;
		if (!$page["moduleID"] || $printing) return false;
		$pages	= array();
		$admin	= ($isAdmin) ? "" : "AND isAdmin = 0";
		$result	= db_query("SELECT name, url FROM pages WHERE moduleID = {$page["moduleID"]} {$admin} AND isInstancePage = 0 ORDER BY precedence");
		while ($r = db_fetch($result)) {
			if ($r["url"] != "/helpdesk/") $pages[$r["url"]] = $r["name"];
		}
		$location = (($location == "bb") || ($location == "cal") || ($location == "docs") || ($location == "staff") || ($location == "helpdesk") || ($location == "contacts")) ? $location : "areas";
		return drawNavigationRow($pages, $location);
	}
	
	function drawNavigationRow($pages, $module="areas", $pq=false) {
		global $_josh;
		$count = count($pages);
		if ($count < 2) return false;
		$return = '<table class="navigation ' . $module . '" cellspacing="1">
			<tr class="' . $module . '-hilite">';
		$cellwidth = round(100 / $count, 2);
		foreach ($pages as $url=>$name) {
			if (($pq && ($_josh["request"]["path_query"] == $url)) || (!$pq && ($_josh["request"]["path"] == $url))) {
				$cell = ' bgcolor="#ffffff"><b>' . $name . '</b>';
			} else {
				$cell = '><a href="' . $url . '">' . $name . '</a>';
			}
			$return .= '<td width="' . $cellwidth . '%"' . $cell . '</td>';
		}
		return $return . '</tr>
			</table>';
	}
		
	function drawHeaderRow($name=false, $colspan=1, $link1text=false, $link1link=false, $link2text=false, $link2link=false) {
		global $_josh, $location, $modules, $page;
		error_debug("drawing header row");
		if (!$name) $name = $page["name"];
		//urls are absolute because it could be used in an email
		$header ='<tr>
				<td class="head ' . $location . '" colspan="' . $colspan . '">
					<div class="head-left">
					';
		if ($location != "login") {
			$header .='<a  href="http://' . $_josh["request"]["host"] . '/' . $_josh["request"]["folder"] . '/">' . $modules[$page["moduleID"]]["name"] . '</a>';
		}
		if ($name) {
			$header .=' &gt; ';
			if ($_josh["request"]["subfolder"]) $header .= '<a href="http://' . $_josh["request"]["host"] . '/' . $_josh["request"]["folder"] . '/' . $_josh["request"]["subfolder"] . '/">' . format_text_human($_josh["request"]["subfolder"]) . '</a> &gt; ';
			$header .= $name;
		}
		$header .= "</div>";
		if ($link2link && $link2text) $header .= '<a class="right" href="' . $link2link . '">' . $link2text . '</a>';
		if ($link1link && $link1text) $header .= '<a class="right" href="' . $link1link . '">' . $link1text . '</a>';
		$header .='</td></tr>';
		return $header;
	}

	function drawName($userID, $name, $date=false, $withtime=false, $separator="<br>") {
		global $_josh, $locale;
		$date = ($date) ? format_date_time($date, "", $separator) : false;
		return '
		<div class="user">
			<a href="http://' . $_josh["request"]["host"] . '/staff/view.php?id=' . $userID . '">' . 
				drawImg($userID) . 
				format_string($name, 20) . 
			'</a>' . $date . '
		</div>';
	}
	
	//much-simplified image drawing function
	function drawImg($userID) {
		global $_josh;
		$filename = '/uploads/staff/' . $userID . '.jpg';
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $filename)) return '';
		return '<img src="http://' .  $_josh["request"]["host"] . $filename . '?' . time() . '" width="320" height="320">';
	}
	
//custom functions - form functions

	function drawSelectUser($name, $selectedID=false, $nullable=false, $length=0, $lname1st=false, $jumpy=false, $text="", $class=false) { 
		$result = db_query("SELECT u.userID, ISNULL(u.nickname, u.firstname) first, u.lastname last FROM intranet_users u WHERE u.isActive = 1 ORDER by last, first");
		if ($jumpy) $jumpy = "location.href='/staff/view.php?id=' + this.value";
		$array = array();
		while ($r = db_fetch($result)) {
			$array[$r["userID"]] = ($lname1st) ? $r["last"] . ", " . $r["first"] : $r["first"] . " " . $r["last"];
		}
		return draw_form_select($name, $array, $selectedID, !$nullable, $class, $jumpy);
	}
	
	function drawThreadTop($title, $content, $userID, $fullname, $date, $editurl=false) {
		global $_josh;
		$return  = '<tr>
				<td height="150" class="left">' . 
				drawName($userID, $fullname, $date, true) . 
				'</td>
				<td class="text"><h1>' . $title . '</h1>';
		if ($editurl) {
			$return .= '<a class="right button floating" href="' . $editurl . '">edit this</a>';
		}
		$return .= '' . 
					str_replace('href="../', 'href="http://' . $_josh["request"]["host"] . '/', $content) . '
				</td>
			</tr>';
		return $return;	
	}
	
	function drawThreadComment($content, $userID, $fullname, $date, $isAdmin=false) {
		global $location;
		$return  = '<tr><td class="left">';
		$return .= drawName($userID, $fullname, $date, true) . '</td>';
		$return .= '<td class="right text ';
		if ($isAdmin) $return .= $location . "-hilite";
		$return .= '" height="80">' . $content . '</td></tr>';
		return $return;
	}
	
	function drawThreadCommentForm($showAdmin=false) {
		global $printing, $isAdmin, $_josh, $user;
		if ($printing) return;
		$return = '
			<a name="bottom"></a>
			<form method="post" action="' . $_josh["request"]["path_query"] . '" onsubmit="javascript:return validate(this);">
			<tr valign="top">
				<td class="left">' . drawName($user["id"], $user["full_name"], false, true) . '</td>
				<td>' . draw_form_textarea("message", "", "mceEditor thread");
		if ($showAdmin && $isAdmin) {
			$return .= '
				<table class="nospacing">
					<tr>
						<td width="16">' . draw_form_checkbox("isAdmin") . '</td>
						<td width="99%">' . drawCheckboxText("isAdmin", "This followup is admin-only (invisible to most users)") . '</td>
					</tr>
				</table>';
		}
		$return .= '
				</td>
			</tr>
			<tr>
				<td class="bottom" colspan="2">' . draw_form_submit("Update Conversation") . '</td>
			</tr>
			</form>';
		return $return;
	}

	function drawEmailHeader() {
		global $_josh, $locale;
		return '<html><head> 
		<style type="text/css">
		' . file_get($_josh["root"] . $locale . "style.css") . '
		</style>
		</head>
		<body class="email">';
	}
	
	function drawEmailFooter() {
		global $_josh;
		return '<div class="emailfooter">This message was generated by the <a href="http://' . $_josh["request"]["host"] . '/">Intranet</a>.</div>
		</body></html>';
	}
	
	//used by drawBottom, also link reorder ajax
	function drawLinks() {
		$links = db_query('SELECT url, text FROM links ORDER BY precedence');
		$return = '';
		while ($l = db_fetch($links)) {
			$return .= '<li><a href="' . $l["url"] . '">' . $l["text"] . '</a></li>';
		}		
		return $return;
	}
	
	function drawSpotlight() {
		if (!$s = db_grab('SELECT id, url, title FROM spotlight ORDER BY precedence')) return;
		$return = '
			<div class="inner">
			<h1>Spotlight</h1>
			<a href="' . $s['url'] . '">';
			
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/spotlight/' . $s['id'] . '.jpg')) {
			$return .= '<img src="/uploads/spotlight/' . $s['id'] . '.jpg" width="320" height="320"><h2 class="has_image">';
		} else {
			$return .= '<h2>';
		}
		
		$return .= $s['title'] . '</h2></a></div>';
		return $return;
	}
	
	function drawTop() {
		global $user, $_josh, $page, $isAdmin, $printing, $locale;
		error_debug("starting top");
		$title = $page["module"] . " > " . $page["name"];
	?><!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo $title?></title>
			<link rel="stylesheet" type="text/css" href="/assets/vendor/bootstrap/dist/css/bootstrap.min.css">
			<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
			<!--[if IE]>
			<link rel="stylesheet" type="text/css" href="<?php echo $locale?>style-ie.css" />
			<![endif]--> 
		</head>
		<body>
		<?php if (!$printing) {?>
			<div class="container">
				<div class="row banner">
					<div class="col-md-4">
						<a href="/bb/"><img src="/assets/img/logo-cc.png" width="240" height="86" class="img-responsive"></a>
					</div>
					<div class="col-md-4">
						<a href="/bb/"><img src="/assets/img/logo-hla.png" width="330" height="64" class="img-responsive"></a>
					</div>
					<div class="col-md-4">
						<a href="/bb/"><img src="/assets/img/logo-hcfa.png" width="186" height="102" class="img-responsive"></a>
					</div>
				</div>
				<div class="row">
				<div id="left" class="col-md-8">
					<div id="help">
					<a class="button left" href="/bb/">
						<i class="glyphicon glyphicon-home"></i>
						Home
					</a>
					<a class="button right" href="<?php echo url_query_add(array("toggleMenuPref"=>"isOpenHelp"), false)?>">
						<i class="glyphicon glyphicon-info-sign"></i>
						<?php if ($user["isOpenHelp"]) { ?>Hide<?php } else {?>Show<?php }?> Help
					</a>
				<?php if ($user["isOpenHelp"]) {
					if ($user["isAdmin"]) {?>
						<a class="button right" href="/admin/edit-help.php?id=<?php echo $page["id"]?>&returnTo=<?php echo urlencode($_josh["request"]["path_query"])?>">
							<i class="glyphicon glyphicon-edit"></i>
							Edit Page Info
						</a>
					<?php }?>
					<div class="text">
					<?php
					echo ($page["helpText"]) ? $page["helpText"] : "No help is available for this page.";
					?>
					</div>
				<?php }?>
				</div>
		<?php }
		if ($_josh["request"]["folder"] == "helpdesk") echo drawNavigationHelpdesk();
		echo drawNavigation();
		$_josh["drawn"]["top"] = true;
		error_debug("finished drawing top");
	}
			
	function drawBottom() {
		global $user, $_josh, $modules, $printing, $areas, $locale, $helpdeskOptions, $helpdeskStatus;
		if (!$printing) {
			
		?>
			</div>
			<div id="right" class="col-md-4">
				<div id="tools">
					<form name="search" method="get" action="/staff/search.php" onSubmit="javascript:return doSearch(this);">
					<a class="right button" href="/index.php?logout=true">
						<i class="glyphicon glyphicon-log-out"></i> Log Out
					</a>
					Hello <b><a href="/staff/view.php?id=<?php echo $user["id"]?>"><?php echo $user["first_name"]?> <?php echo $user["last_name"]?></b></a>.
		            <input type="text" class="form-control" name="q" placeholder="Search Staff">
					</form>
					
					<ul class="links">
						<?php echo drawLinks(); ?>
					</ul>
				</div>
				
				<div id="spotlight">
					<?php echo drawSpotlight(); ?>
				</div>
		<?php 
			            
		foreach ($modules as $module) {
			if ($module["pallet"]) {
				if ($module["url"] == "/bb/") {
					$index = "isOpenBulletinBoard";
				} elseif ($module["url"] == "/cal/") {
					$index = "isOpenCalendar";
				} elseif ($module["url"] == "/docs/") {
					$index = "isOpenDocuments";
				} elseif ($module["url"] == "/areas/") {
					$index = "isOpenAreas";
				} elseif ($module["url"] == "/staff/") {
					$index = "isOpenStaff";
				}
			?>
			<table class="right" cellspacing="1">
				<tr>
					<td colspan="2" class="head <?php echo str_replace("/", "", $module["url"])?>">
						<div class="head-left"><a href="<?php echo $module["url"]?>"><?php echo $module["name"]?></a></div>
					</td>
				</tr>
				<?php include($_josh["root"] . $module["pallet"])?>
			</table>
			<?php }
		}?>

			<table class="right" cellspacing="1">
				<tr>
					<td colspan="2" class="head docs">
						<div class="head-left"><a href="https://ccatalyst.sharepoint.com/HCFACCIT/Forms/AllItems.aspx" target="_blank">Documents</a></div>
					</td>
				</tr>
				<tr>
					<td style="background-color:#eee; text-align: center; padding: 40px 0;">
						<a href="https://ccatalyst.sharepoint.com/HCFACCIT/Forms/AllItems.aspx" target="_blank" style="color: #333;font-size:15px;line-height: 1.3;">Documents have moved to<br>Microsoft SharePoint</a>
					</td>
				</tr>
			</table>
						<div id="footer">page rendered in <?php echo format_time_exec()?></div>
					</div>
				</div>
			</div>
			<script src="/assets/vendor/jquery/dist/jquery.min.js"></script>
			<script src="/assets/vendor/TableDnD/dist/jquery.tablednd.min.js"></script>
			<script src="/assets/vendor/tinymce/tinymce.jquery.min.js"></script>
			<script src="/assets/js/javascript.js"></script>
		</body>
	</html>
	<?php 
	}
	db_close();
}