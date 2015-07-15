<?php  include("../include.php");

if ($posting) {
	if (isset($_GET["id"])) { //edit a funder			
		//clear funder interests
		db_query("DELETE FROM Resources_Funders_Geographic_Interests WHERE funderID = " . $_GET["id"]);
		db_query("DELETE FROM Resources_Funders_Program_Interests    WHERE funderID = " . $_GET["id"]);
		
		db_query("UPDATE resources_funders SET
			name           = '"  . $_POST["name"] . "',
			funderTypeID   = "  . $_POST["cboFunderTypes"] . ",
			funderStatusID = "  . $_POST["cboFunderStatuses"] . ",
			staffID        = "  . $_POST["cboStaff"] . "
			WHERE funderID = "  . $_GET["id"]);
			
	} else { //add a funder
		db_query("INSERT into resources_funders (
			name,
			funderTypeID,
			funderStatusID,
			staffID
		) VALUES (
			'"  . $_POST["name"] . "',
			"  . $_POST["cboFunderTypes"] . ",
			"  . $_POST["cboFunderStatuses"] . ",
			"  . $_POST["cboStaff"] . "
		);");
		
		//set up funder id to be update interests, redirect
		$_GET = db_grab("SELECT max(funderID) id FROM resources_funders");
	}
	
	//insert funder interests
	while (list($key, $value) = each($_POST)) {
		@list($control, $type, $odjectid) = explode("_", $key);
		if ($control == "chk") {
			if ($type == "geographicArea") {
				db_query("INSERT INTO resources_funders_geographic_interests (
					funderID,
					geographicAreaID
				) VALUES (
					" . $_GET["id"] . ",
					" . $odjectid . "
				);");
			} else {
				db_query("INSERT INTO resources_funders_program_interests (
					funderID,
					programID
				) VALUES (
					" . $_GET["id"] . ",
					" . $odjectid . "
				);");
			}
		}
	}
	
	//go to view funder
	url_change("funder_view.php?id=" . $_GET["id"]);
}

drawTop();

	
if (isset($_GET["id"])) { //edit a funder
	$r = db_grab("SELECT 
		f.name, 
		f.funderTypeID, 
		f.funderStatusID,
		f.staffID
	FROM resources_funders f
	WHERE funderID = " . $_GET["id"]);
}
?>

<table class="left" cellspacing="1">
	<form name="frmFunder" action="<?php echo $_josh["request"]["path_query"]?>" method="post" onsubmit="javascript:return validate(this);">
	<?php if (isset($_GET["id"])) {
		echo drawHeaderRow("Edit Funder", 2);
	} else {
		echo drawHeaderRow("Add Funder", 2);
	}?>
	<tr>
		<td width="18%" class="gray">Name:</td>
		<td width="82%"><?php echo @draw_form_text("name", $r["name"]);?></td>
	</tr>
	<tr>
		<td class="gray"><nobr>Type:</nobr></td>
		<td><?php echo draw_form_select("cboFunderTypes", "SELECT funderTypeID, funderTypeDesc FROM Resources_Funders_Types", @$r["funderTypeID"]);?></td>
	</tr>
	<tr>
		<td class="gray"><nobr>Status:</nobr></td>
		<td><?php echo draw_form_select("cboFunderStatuses", "SELECT funderStatusID, funderStatusDesc FROM Resources_Funders_Statuses", @$r["funderStatusID"]);?></td>
	</tr>
	<tr>
		<td class="gray"><nobr>Funder Contact:</nobr></td>
		<td><?php echo drawSelectUser("cboStaff", @$r["staffID"]);?></td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
			<table width="100%">
				<tr>
					<td width="49%" valign="top">
						<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE" class="small">
							<tr>
								<td width="100%" class="head" colspan="2">Program Interests</td>
							</tr>
							<?php
							$selected_programs = array();
							if (isset($_GET["id"])) {
								$result_programs_selected = db_query("SELECT programID FROM resources_funders_program_interests WHERE funderID = " . $_GET["id"]);
								while ($rp_s = db_fetch($result_programs_selected)) $selected_programs[$rp_s["programID"]] = true;
							}
							$result_programs = db_query("SELECT programID, programDesc FROM intranet_programs ORDER BY programDesc");
							while ($rp = db_fetch($result_programs)) {?>
							<tr>
								<td><input type="checkbox" name="chk_program_<?php echo $rp["programID"]?>"<?php if(@$selected_programs[$rp["programID"]]) {?> checked<?php }?>></td>
								<td width="99%"><?php echo $rp["programDesc"]?></td>
							</tr>
							<?php }?>
						</table>
					</td>
					<td width="20"></td>
					<td width="49%" valign="top">
						<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE" class="small">
							<tr>
								<td width="100%" class="head" colspan="2">Geographic Interests</td>
							</tr>
							<?php
							$selected_areas = array();
							if (isset($_GET["id"])) {
								$result_geographic_areas_selected = db_query("SELECT geographicAreaID FROM resources_funders_geographic_interests WHERE funderID = " . $_GET["id"]);
								while ($rg_s = db_fetch($result_geographic_areas_selected)) $selected_areas[$rg_s["geographicAreaID"]] = true;
							}
							$result_geographic_areas = db_query("SELECT geographicAreaID, geographicAreaDesc FROM intranet_geographic_areas ORDER BY geographicAreaDesc");
							while ($rg = db_fetch($result_geographic_areas)) { ?>
							<tr>
								<td><input type="checkbox" name="chk_geographicArea_<?php echo $rg["geographicAreaID"]?>"<?php if(@$selected_areas[$rg["geographicAreaID"]]) {?> checked<?php }?>></td>
								<td width="99%"><?php echo $rg["geographicAreaDesc"]?></td>
							</tr>
							<?php }?>
						</table>
					</td>
				</tr>
			</table>
			<br><br>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2"><?php
			if (isset($_GET["id"])) {
				echo draw_form_submit("save changes");
			} else {
				echo draw_form_submit("add funder");
			}
		?></td>
	</tr>
	</form>
</table>
<?php drawBottom(); ?>