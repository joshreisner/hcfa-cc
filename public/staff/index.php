<?php
include("include.php");

if (!isset($_GET["id"])) $_GET["id"] = 1;

drawTop();

?>
<table class="navigation staff" cellspacing="1">
	<tr class="staff-hilite">
		<td width="20%"<?php if ($_GET["id"] == 1) {?> class="selected"<?php }?>><?php if ($_GET["id"] != 1) {?><a href="index.php?id=1"><?php }?>A - E</a></td>
		<td width="20%"<?php if ($_GET["id"] == 2) {?> class="selected"<?php }?>><?php if ($_GET["id"] != 2) {?><a href="index.php?id=2"><?php }?>F - J</a></td>
		<td width="20%"<?php if ($_GET["id"] == 3) {?> class="selected"<?php }?>><?php if ($_GET["id"] != 3) {?><a href="index.php?id=3"><?php }?>K - O</a></td>
		<td width="20%"<?php if ($_GET["id"] == 4) {?> class="selected"<?php }?>><?php if ($_GET["id"] != 4) {?><a href="index.php?id=4"><?php }?>P - T</a></td>
		<td width="20%"<?php if ($_GET["id"] == 5) {?> class="selected"<?php }?>><?php if ($_GET["id"] != 5) {?><a href="index.php?id=5"><?php }?>U - Z</a></td>
	</tr>
</table>

<?php
if ($_GET["id"] == 1) {
	$letters = "u.lastname like 'a%' or 
				u.lastname like 'b%' or 
				u.lastname like 'c%' or 
				u.lastname like 'd%' or 
				u.lastname like 'e%'";
} elseif ($_GET["id"] == 2) {
	$letters = "u.lastname like 'f%' or 
				u.lastname like 'g%' or 
				u.lastname like 'h%' or 
				u.lastname like 'i%' or 
				u.lastname like 'j%'";
} elseif ($_GET["id"] == 3) {
	$letters = "u.lastname like 'k%' or 
				u.lastname like 'l%' or 
				u.lastname like 'm%' or 
				u.lastname like 'n%' or 
				u.lastname like 'o%'";
} elseif ($_GET["id"] == 4) {
	$letters = "u.lastname like 'p%' or 
				u.lastname like 'q%' or 
				u.lastname like 'r%' or 
				u.lastname like 's%' or 
				u.lastname like 't%'";
} else {
	$letters = "u.lastname like 'u%' or 
				u.lastname like 'v%' or 
				u.lastname like 'w%' or 
				u.lastname like 'x%' or 
				u.lastname like 'y%' or 
				u.lastname like 'z%'";
}

echo drawStaffList("u.isactive = 1 and (" . $letters . ")");

drawBottom();