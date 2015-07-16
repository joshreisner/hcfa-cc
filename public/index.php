<?php
include("include.php");
$redirect = false;
if (isset($_GET["logout"])) {
	error_debug("<b>index.php</b> Logging Out");
	cookie("last_login");
	$redirect = "/";
} elseif (isset($_COOKIE["last_login"]) && login($_COOKIE["last_login"], "", true)) { //log in with last login
	error_debug("<b>index.php</b> Cookie Found (good)");
	$redirect = (!empty($_GET["goto"])) ? $_GET["goto"] : $user["url"];
} elseif ($posting) { //logging in
	error_debug("<b>index.php</b> Posting");
	if (login($_POST["email"], $_POST["password"])) {
		error_debug("<b>index.php</b> Login successful");
		cookie("last_login", $_POST["email"]);
		$redirect = (!empty($_POST["goto"])) ? $_POST["goto"] : $user["url"];
   	} else {
		error_debug("<b>index.php</b> Login unsuccessful");
		$redirect = "/";
    }
}
if ($redirect) url_change($redirect);
include("_hcfa-cc/login.php");