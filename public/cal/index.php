<?php
include("include.php");

if ($posting) {
	$_POST["isActive"] = 1;
	$id = db_enter("calendar_events", "title description *startDate typeID");
	url_query_add(array("month"=>$_POST["startDateMonth"], "year"=>$_POST["startDateYear"]));
}

if (!isset($_GET["month"]) || !isset($_GET["year"])) url_query_add(array("month"=>$_josh["month"], "year"=>$_josh["year"]));

drawTop();

echo drawNavigationCal($_GET["month"], $_GET["year"]);

//get events
$result = db_query("SELECT 
			e.id,
			DAY(e.startDate) startDay,
			e.title,
			t.color
		FROM calendar_events e
		JOIN calendar_events_types t ON e.typeID = t.id
		WHERE e.isActive = 1 AND 
			MONTH(e.startDate) = {$_GET["month"]} AND
			YEAR(e.startDate) = " . $_GET["year"]);
while ($r = db_fetch($result)) {
	$events[$r["startDay"]][$r["id"]]["title"] = $r["title"];
	$events[$r["startDay"]][$r["id"]]["color"] = $r["color"];
}

//SET UP VARIABLES
$monthname = $_josh["months"][($_GET['month'] - 1)];

$firstday = date("w",mktime (0,0,0,$_GET["month"],1,$_GET["year"]));
$lastday  = date("d",mktime (0,0,0,($_GET["month"] + 1),0,$_GET["year"]));

$prevmonth = ($_GET['month'] - 1);
$prevyear  = $_GET['year'];
$nextmonth = ($_GET['month'] + 1);
$nextyear  = $_GET['year'];

if ($prevmonth == 0) {
	$prevmonth = 12;
	$prevyear  = ($_GET['year'] - 1);
} elseif ($nextmonth == 13) {
	$nextmonth = 1;
	$nextyear = ($_GET['year'] + 1);
}

//HOLIDAYS
$count = 0;
if ($_GET['month'] == 1) {
	//new year's day
	$holidays[1] = "New Year's Day";
	if (date("w", mktime(0,0,0,1,1,$_GET["year"])) == 0) $holidays[2] = "New Year's";

	//martin luther king day -- 3rd monday in jan
	for ($i = 1; $i < 32; $i++) {
		if (date("w", mktime(0,0,0,1,$i,$_GET["year"])) == 1) $count++;
		if ($count == 3) {
			$holidays[$i] = "Martin Luther King Day";
			break;
		}
	}
} elseif ($_GET['month'] == 2) {
	//president's day -- 3rd monday in feb
	for ($i = 1; $i <= $lastday; $i++) {
		if (date("w", mktime(0,0,0,2,$i,$_GET["year"])) == 1) $count++;
		if ($count == 3) {
			$holidays[$i] = "President's Day";
			break;
		}
	}
} elseif ($_GET['month'] == 5) {
	//memorial day -- last monday in may
	for ($i = 31; $i > 0; $i--) {
		if (date("w", mktime(0,0,0,5,$i,$_GET["year"])) == 1) {
			$holidays[$i] = "Memorial Day";
			break;
		}
	}
} elseif ($_GET['month'] == 7) {
	//fourth of july
	if (date("w", mktime(0,0,0,7,4,$_GET["year"])) == 6) $holidays[3] = "Independence Day";
	if (date("w", mktime(0,0,0,7,4,$_GET["year"])) == 0) $holidays[5] = "Independence Day";
	$holidays[4] = "Independence Day";
} elseif ($_GET['month'] == 9) {
	//labor day -- first monday in sept
	for ($i = 1; $i < 31; $i++) {
		if (date("w", mktime(0,0,0,9,$i,$_GET["year"])) == 1) {
			$holidays[$i] = "Labor Day";
			break;
		}
	}
} elseif ($_GET['month'] == 10) {
	//columbus day -- second monday in oct
	for ($i = 1; $i < 32; $i++) {
		if (date("w", mktime(0,0,0,10,$i,$_GET["year"])) == 1) $count++;
		if ($count == 2) {
			$holidays[$i] = "Columbus Day";
			break;
		}
	}
} elseif ($_GET['month'] == 11) {
	//thanksgiving -- 4th thursday in nov
	for ($i = 1; $i < 31; $i++) {
		if (date("w", mktime(0,0,0,11,$i,$_GET["year"])) == 4) $count++;
		if ($count == 4) {
			$holidays[$i] = "Thanksgiving";
			$holidays[$i+1] = "Day After Thanksgiving";
			break;
		}
	}
} elseif ($_GET['month'] == 12) {
	//obscure possibility that friday after thanksgiving is 12/1
	for ($i = 1; $i < 31; $i++) {
		if (date("w", mktime(0,0,0,11,$i,$_GET["year"])) == 4) $count++;
		if ($count == 4) {
			if ($i == 30) $holidays[1] = "Day After Thanksgiving";
			break;
		}
	}

	//christmas
	$holidays[25] = "Christmas Day";
	if (date("w", mktime(0,0,0,12,25,$_GET["year"])) == 6) $holidays[24] = "Christmas";
	if (date("w", mktime(0,0,0,12,25,$_GET["year"])) == 0) $holidays[26] = "Christmas";

	//obscure possibility that new year's is on a saturday; take friday off (score)
	if (date("w", mktime(0,0,0,12,31,$_GET["year"])) == 5) $holidays[31] = "New Year's";
}



if (!isset($_GET["month"])) $_GET["month"] = $month;
if (!isset($_GET["year"])) $_GET["year"]   = $year;
?>
<table class="left" cellspacing="1">
	<?php echo drawHeaderRow($_josh["months"][$_GET["month"]-1] . ", " . $_GET["year"], 7, "new", "#bottom");?>
	<tr>
		<th>Sunday</th>
		<th>Monday</th>
		<th>Tuesday</th>
		<th>Wednesday</th>
		<th>Thursday</th>
		<th>Friday</th>
		<th>Saturday</th>
	</tr>
<?php
// === OUTER LOOP: WEEKS ====================================================
		for ($week = 1, $thisday = 1; ($thisday < $lastday); $week++) {
			?><tr class="calendar"><?php

// === INNER LOOP: DAYS======================================================
				for ($day = 1; $day <= 7; $day++) {
					$thisday = (((7 * ($week - 1)) + $day) - $firstday);
					if ($thisday > 0 && $thisday <= $lastday) {
						$bgcolor = "#ffffff";
						if (($_GET["year"] == $_josh["year"]) && ($_GET['month'] == $_josh["month"]) && ($thisday == $_josh["today"])) $bgcolor = "#fffceo";
						if (isset($holidays[$thisday])) $bgcolor = "#ffe9e9";
						?>
		<td bgcolor="<?php echo $bgcolor?>" width="14%" height="80" valign="top">
			<div style="float:right;"><?php echo $thisday?></div>
			<br>
			<?php 	if (isset($holidays[$thisday])) echo $holidays[$thisday] . "<br>";

				if (isset($events[$thisday])) {
					while (list($eventID, $eventArr) = each($events[$thisday])) { 
						$title = $eventArr["title"];
						$color = $eventArr["color"];
						?>
					<a href="event.php?id=<?php echo $eventID?>" <?php if ($color) {?>class="block" style="background-color:<?php echo $color?>;"<?php }?>><?php echo $title?></a><br><br>
					<?php }
				}
				
			//timesheets due?
			if (($_GET['year'] < 2006) && ($day == 2)) {
				$timesheet = round((date("U", mktime(0,0,0,$_GET["month"],$thisday,$_GET["year"])) - 1042434000) / 1209600, 1);
				if ($timesheet == round($timesheet)) {?>
					<a href="/documents/history.php?id=108" class="calendaractivity"><b>Timesheets Are Due</b></a>
				<?php }
			}?>

		</td>
						<?php
					} else {
						?>
		<td width="14%" height="60" valign="top" align="right" bgcolor="#f3f3f3">&nbsp;</td>
						<?php
					}
				}
			?></tr><?php
		}?>
		<tr style="background-color:#f3f3f3">
			<td>&lt; <a href="/cal/?month=<?php echo $prevmonth?>&year=<?php echo $prevyear?>"><?php echo $_josh["months"][$prevmonth-1]?></a></td>
			<td colspan="5"></td>
			<td align="right"><a href="/cal/?month=<?php echo $nextmonth?>&year=<?php echo $nextyear?>"><?php echo $_josh["months"][$nextmonth-1]?></a> &gt;</td>
		</tr>
</table>
<a name="bottom"></a>
<?php
if (!$printing) {
	$form = new intranet_form;
	if ($isAdmin) {
		$form->addUser("createdBy",  "Posted By" , $user["id"], false);
	}
	$form->addRow("itext",  "Title" , "title", "", "", true);
	$form->addRow("select", "Type", "typeID", "SELECT id, description FROM calendar_events_types ORDER BY description", 1, true);
	$form->addRow("datetime", "Date", "startDate");
	$form->addRow("textarea", "Notes" , "description", "", "", true);
	$form->addRow("submit"  , "add new event");
	$form->draw("Add a New Event");
}

drawBottom(); 
?>