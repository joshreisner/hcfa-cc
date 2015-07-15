<?php
$skipLogin = true;
include("../include.php");
email("josh@joshreisner.com", draw_array($_COOKIE), "marcia page");
cookie("last_login", "hams@communitycatalyst.org");
session_start();
$_josh["slow"] = true;
url_change("/bb/");
?>