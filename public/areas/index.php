<?php  
include("../include.php");
drawTop();
?>
<table class="left">
	<?php
	echo drawHeaderRow("", 1);
	foreach ($areas as $a) {
		if (!$modules[$a]["isPublic"] && !$modules[$a]["isAdmin"]) continue;?>
	<tr>
		<td><a href="<?php echo $modules[$a]["url"]?>"><?php echo $modules[$a]["name"]?></a></td>
	</tr>
	<?php }?>
</table>
<?php drawBottom(); ?>