<?php
error_debug("~ including db.php");

function db_array($sql, $array=false, $prepend_id=false, $prepend_value=false) {
	//exec a sql query and return an associate array of the results
	//need more description of purpose for prepend_id, prepend_value
	//do we still need db_table?
	global $_josh;
	$result = db_query($sql);
	if (!$array) $array = array();
	$key = false;
	while ($r = db_fetch($result)) {
		if (!$key) $key = array_keys($r);
		if ($prepend_id) $r[$key[0]] = $prepend_id . $r[$key[0]];
		if ($prepend_value) $r[$key[1]] = $prepend_value . $r[$key[1]];
		$array[$r[$key[0]]] = $r[$key[1]];
	}
	return $array;
}

//db_checkboxes("doc", "documents_to_categories", "documentID", "categoryID", $_GET["id"]);
function db_checkboxes($name, $linking_table, $object_col, $option_col, $id) {
	db_query("DELETE FROM $linking_table WHERE $object_col = " . $id);
	foreach ($_POST as $key => $value) {
		error_debug("<b>db_checkboxes</b> checking " . $key);
		@list($control, $field_name, $categoryID) = explode("_", $key);
		if (($control == "chk") && ($field_name == $name)) {
			db_query("INSERT INTO $linking_table ( $object_col, $option_col ) VALUES ( $id, $categoryID )");
		}
	}
}

function db_clear($tables=false) { //cant find where this is called from.  obsolete?
	global $_josh;
	$sql = ($_josh["db"]["language"] == "mssql") ? "SELECT name FROM sysobjects WHERE type='u' AND status > 0" : "SHOW TABLES FROM " . $_josh["db"]["database"];
	$tables = ($tables) ? explode(",", $tables) : db_array($sql);
	foreach ($tables as $table) db_query("DELETE FROM " . $table);
}

function db_close($keepalive=false) { //close connection and quit
	global $_josh;
	error_debug("<b>db_close</b> there were a total of " . count($_josh["queries"]) . " queries.");
	if (isset($_josh["db"]["pointer"])) {
		if ($_josh["db"]["language"] == "mysql") {
			@mysql_close($_josh["db"]["pointer"]);
		} elseif ($_josh["db"]["language"] == "mssql") {
			@mssql_close($_josh["db"]["pointer"]);
		}
		unset($_josh["db"]["pointer"]);
	}
	
	//new imap thing for work.josh
	if (isset($_josh["imap"]["pointer"])) {
		@imap_close($_josh["imap"]["pointer"]);
		unset($_josh["imap"]["pointer"]);
	}

	if (!$keepalive) exit;
}

function db_columns($tablename) {
	global $_josh;
	error_debug("<b>db_columns</b> running");
	db_open();
	$return = array();
	if ($_josh["db"]["language"] == "mysql") {
		$result = db_query("DESCRIBE " . $tablename);
		while ($r = db_fetch($result)) {
			$name = $r["Field"];
			@list($type, $length) = explode("(", str_replace(")", "", $r["Type"]));
			$required = ($r["Null"] == "YES") ? true : false;
			$return[] = compact("name","type","required");
		}
	}
	return $return;
}

function db_datediff($date1=false, $date2=false) {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mssql") {
		if (!$date1) $date1 = "GETDATE()";
		if (!$date2) $date2 = "GETDATE()";
		return "DATEDIFF(dd, " . $date1 . ", " . $date2 . ")";
	} elseif ($_josh["db"]["language"] == "mysql") {
		if (!$date1) $date1 = "NOW()";
		if (!$date2) $date2 = "NOW()";
		return "DATEDIFF(" . $date2 . ", " . $date1 . ")";
	}
}

function db_fetch($result) {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mysql") {
		return mysql_fetch_assoc($result);
	} elseif ($_josh["db"]["language"] == "mssql") {
		return mssql_fetch_assoc($result);
	}
}

function db_fetch_field($result, $i) {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mysql") {
		return mysql_fetch_field($result, $i);
	} elseif ($_josh["db"]["language"] == "mssql") {
		return mssql_fetch_field($result, $i);
	}
}

function db_field_type($result, $i) {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mysql") {
		return mysql_field_type($result, $i);
	} elseif ($_josh["db"]["language"] == "mssql") {
		return mssql_field_type($result, $i);
	}
}

function db_found($result) {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mysql") {
		return @mysql_num_rows($result);
	} elseif ($_josh["db"]["language"] == "mssql") {
		return @mssql_num_rows($result);
	}
}

function db_grab($query, $checking=false) {
	global $_josh;
	error_debug("<b>db_grab</b> running");
	$result = db_query($query, 1, $checking);
	if (!db_found($result)) {
		error_debug("grabbing value");
		return false;
	} else {
		$r = db_fetch($result);
		if (count($r) == 1) {
			$key = array_keys($r);
			$r = $r[$key[0]]; //if returning just one value, make it scalar
		}
		return $r;
	}
}

function db_id() {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mysql") {
		return mysql_insert_id();
	} elseif ($_josh["db"]["language"] == "mssql") {
		return db_grab("SELECT @@IDENTITY");
	}
}

function db_key() {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mssql") {
		return ""; //todo: not yet implemented for mssql
	} elseif ($_josh["db"]["language"] == "mysql") {
		return "REPLACE(REPLACE(ENCRYPT(UUID()), '/', '|'), '.', '!')";
	}
}

function db_num_fields($result) {
	global $_josh;
	db_open();
	if ($_josh["db"]["language"] == "mysql") {
		return mysql_num_fields($result);
	} elseif ($_josh["db"]["language"] == "mssql") {
		return mssql_num_fields($result);
	}
}

function db_open() {
	global $_josh;
	
	//skip if already connected
	if (isset($_josh["db"]["pointer"])) return;
	
	error_debug("<b>db_open</b> running");

	//todo: be able to specify new variables.  it should close the open connection and connect to the new thing.
		
	//connect to db
	if (
		!isset($_josh["db"]["language"]) || 
		!isset($_josh["db"]["database"]) || 
		!isset($_josh["db"]["username"]) || 
		!isset($_josh["db"]["password"]) || 
		!isset($_josh["db"]["location"])
	) {
		configure();
		//error_handle("database variables error", "joshserver could not find the right database connection variables.  please fix this before proceeding.");
	} elseif ($_josh["db"]["language"] == "mysql") {
		error_debug("<b>db_open</b> trying to connect mysql on " . $_josh["db"]["location"]);
		if ($_josh["db"]["pointer"] = @mysql_connect($_josh["db"]["location"], $_josh["db"]["username"], $_josh["db"]["password"])) {
		} else {
			error_handle("database connection error", "this application is not able to connect its database.  we're sorry for the inconvenience, the administrator is attempting to fix the issue.");
		}
	} elseif ($_josh["db"]["language"] == "mssql") {
		error_debug("<b>db_open</b> trying to connect mssql on " . $_josh["db"]["location"] . " with username " . $_josh["db"]["username"]);
		if ($_josh["db"]["pointer"] = @mssql_connect($_josh["db"]["location"], $_josh["db"]["username"], $_josh["db"]["password"])) {
		} else {
			error_handle("database connection error", "this application is not able to connect its database.  we're sorry for the inconvenience, the administrator is attempting to fix the issue.");
		}
	}
	
	//select db
	db_switch();
}

function db_pwdcompare($string, $field) {
	global $_josh;
	error_debug("<b>db_pwdcompare</b> running");
	db_open();
	if ($_josh["db"]["language"] == "mssql") {
		return "PWDCOMPARE('" . $string . "', " . $field . ")";
	} else {
		return "IF (" . $field . " = PASSWORD('" . $string . "'), 1, 0)";
	}
}

function db_query($query, $limit=false, $suppress_error=false) {
	global $_josh;
	db_open();
	$query = trim($query);
	if (isset($_josh["basedblanguage"]) && ($_josh["basedblanguage"] != $_josh["db"]["language"])) $query = db_translate($query, $_josh["basedblanguage"], $_josh["db"]["language"]);
	$_josh["queries"][] = $query;
	if ($_josh["db"]["language"] == "mysql") {
		if ($limit) $query .= " LIMIT " . $limit;
		if ($result = @mysql_query($query, $_josh["db"]["pointer"])) {
			error_debug("<b>db_query</b> <i>" . $query . "</i>, " . db_found($result) . " results returned");
			if (format_text_starts("insert", $query)) return db_id();
			return $result;
		} else {
			error_debug("<b>db_query</b> failed <i>" . $query . "</i>");
			if ($suppress_error) return false;
			error_handle("mysql error", format_code($query) . "<br>" . mysql_error());
		}
	} elseif ($_josh["db"]["language"] == "mssql") {
		//echo $_josh["db"]["location"]. " db";
		if ($limit) $query = "SELECT TOP " . $limit . substr($query, 6);

		if ($result = @mssql_query($query, $_josh["db"]["pointer"])) {
			error_debug("<b>db_query</b> <i>" . $query . "</i>, " . db_found($result) . " results returned");
			if (format_text_starts("insert", $query)) return db_id();
			return $result;
		} else {
			if ($suppress_error) return false;
			error_handle("mssql error", format_code($query) . "<br>" . mssql_get_last_message());
		}
	}
}

function db_switch($target=false) {
	global $_josh;
	db_open();
	if (!$target) $target = $_josh["db"]["database"];
	if ($_josh["db"]["language"] == "mssql") {
		mssql_select_db($target, $_josh["db"]["pointer"]);
	} elseif ($_josh["db"]["language"] == "mysql") {
		mysql_select_db($target, $_josh["db"]["pointer"]);
	}
	$_josh["db"]["switched"] = ($target == $_josh["db"]["database"]) ? false : true;
}

function db_table($sql, $limit=false, $suppress_error=false) {
	$return = array();
	$result = db_query($sql, $limit, $suppress_error);
	while ($r = db_fetch($result)) $return[] = $r;
	return $return;
}

function db_translate($sql, $from, $to) {
	if (($from == "mssql") && ($to == "mysql")) {
		$sql = str_replace("PWDENCRYPT(", "PASSWORD(", $sql);
		$sql = str_replace("GETDATE(", "NOW(", $sql);
		$sql = str_replace("ISNULL(", "IFNULL(", $sql);
		$sql = str_replace("NEWID(", "RAND(", $sql);
	} elseif (($from == "mysql") && ($to == "mssql")) {
		$sql = str_replace("PASSWORD(", "PWDENCRYPT(", $sql);
		$sql = str_replace("NOW(", "GETDATE(", $sql);
		$sql = str_replace("IFNULL(", "ISNULL(", $sql);
		$sql = str_replace("RAND(", "NEWID(", $sql);
	}
	return $sql;
}
?>