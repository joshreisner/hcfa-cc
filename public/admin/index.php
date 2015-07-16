<?php
include("../include.php");

drawTop();

echo drawTableStart();
echo drawHeaderRow();
echo drawEmptyResult("This is the admin page, which hasn't been created yet.");
echo drawTableEnd();

drawBottom();