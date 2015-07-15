<?php
include("../include.php");

//empty query is confusing
if (isset($_GET["q"]) && empty($_GET["q"])) url_change("/contacts/");

drawTop();


function formatArrayForText($array) {
	if (count($array) > 1) {
		$last = array_pop($array);
		return implode(", ", $array) . " and " . $last;
	} else {
		return $array[0];
	}
}
?>
<table class="message">
	<form method="get" action="./" name="mainsearchform">
	<tr>
		<td class="gray">Look for <input type="text" value="<?php echo @$_GET["q"]?>" name="q" class="field" size="34"></td>
	</tr>
	</form>
</table>
<?php
if (isset($_GET["q"])) {
	//assemble where clause
	$searchTerms = explode(" ", $_GET["q"]);
	$counter = 0;
	$skips = array();
	$where = array();
	foreach ($searchTerms as $searchTerm) {
		$searchTerm = str_replace("'", "''", $searchTerm);
		if (in_array($searchTerm, $ignored_words)) {
			$skips[] = $searchTerm;
		} else {
			$terms[] = $searchTerm;
			$where[] = "w$counter.word = '$searchTerm'";
			$joins[] = "INNER JOIN intranet_instances_to_words i2w$counter ON i.id = i2w$counter.instanceID INNER JOIN intranet_words w$counter ON i2w$counter.wordID = w$counter.id";
			$counter++;
		}
	}
	if (count($skips)) {
		if (count($skips) == 1) {
			echo drawServerMessage("<b>Note:</b> The word {$skips[0]} was ignored in your search.");
		} else {
			echo drawServerMessage("<b>Note:</b> The words " . formatArrayForText($skips) . " were ignored in your search.");
		}
	}
	//$where[] = "o.isActive = 1";
	if (count($where)) {
		$where = implode(" AND ", $where);
		$joins = implode(" ", $joins);
		
		$needle = join('|',$searchTerms);
		
		$result = db_query("
						SELECT
							o.id,
							o.isActive,
							i.varchar_01 firstname,
							i.varchar_02 lastname,
							i.varchar_04 organization,
							i.varchar_08 phone,
							i.createdOn last_updated,
							i.createdBy userID
						FROM intranet_objects o
						INNER JOIN intranet_instances i ON i.ID = o.instanceCurrentID
						$joins
						WHERE $where
						ORDER BY 
								i.varchar_02, 
								i.varchar_01"); 
								?>
		<table class="left" cellspacing="1">
			<?php
			if (db_found($result)) {
				echo drawHeaderRow("Contacts containing <i>" . formatArrayForText($terms) . "</i>", 4);?>
			<tr>
				<th>Name</th>
				<th>Company</th>
				<th>Phone</th>
			</tr>
			<?php while ($c = db_fetch($result)) {
					$c["firstname"]  = preg_replace("/($needle)/i","<font style='background-color:#FFFFBB;padding:1px;'><b>\\0</b></font>", $c["firstname"]);
					$c["lastname"]  = preg_replace("/($needle)/i","<font style='background-color:#FFFFBB;padding:1px;'><b>\\0</b></font>", $c["lastname"]);
					$c["organization"] = preg_replace("/($needle)/i","<font style='background-color:#FFFFBB;padding:1px;'><b>\\0</b></font>", $c["organization"]);
					?>
				<tr <?php if(!$c["isActive"]){?>class="deleted"<?php }?>>
					<td><a href="contact.php?id=<?php echo $c["id"]?>"><?php echo $c["lastname"]?>, <?php echo $c["firstname"]?></a></td>
					<td><?php echo $c["organization"]?></td>
					<td><?php echo $c["phone"]?></td>
				</tr>
				<?php } 
			} else {
				echo drawHeaderRow("Empty Result", 4);
				echo drawEmptyResult("No contact records contain <i>" . formatArrayForText($terms) . "</i>.");
			}
			?>
		<tr>
			<td class="bottom" colspan="4">
				<?php echo draw_form_button("Add a new contact", "contact_edit.php")?>
			</td>
		</tr>
	</table>
	<?php }
}?>
<script>
	<!--
	document.mainsearchform.q.focus();
	//-->
</script>
<?php drawBottom();?>