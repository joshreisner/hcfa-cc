<?php //function format_title
error_debug("~ including format.php");

function format_accents($string) {
	$string = str_replace("�", "&rsquo;", $string);
	$string = str_replace("�", "&mdash;", $string);
	$string = str_replace("�", "&ldquo;", $string);
	$string = str_replace("�", "&rdquo;", $string);
	$string = str_replace("�", "&uuml;",  $string);
	$string = str_replace("�", "&ccedil;", $string);
	return $string;
}

function format_array_text($array) {
	if (count($array) > 1) {
		$last = array_pop($array);
		return implode(", ", $array) . " and " . $last;
	} else {
		return $array[0];
	}
}

function format_binary($blob) {
	global $_josh;
	if ($_josh["db"]["language"] == "mssql") {
		$return = unpack("H*hex", $blob);
		return "0x" . $return["hex"];
	} elseif ($_josh["db"]["language"] == "mysql") {
		return "'" . addslashes($blob) . "'";
	}
}

function format_boolean($value, $options="Yes|No") {
	$options = explode("|", $options);
	if ($value) return $options[0];
	return $options[1];
}

function format_check($variable, $type="int") {
	//alias
	return format_verify($variable, $type);
}

function format_code($code) {
	return "<div class='josh_code'>" . nl2br(str_replace("\t", "&nbsp;", $code)) . "</div>";
}

function format_date($timestamp, $error="", $format="M d, Y", $todaytime=true, $relativetime=true) {
	global $_josh;

	if (strToUpper($format) == "SQL") {
		$format = "Y-m-d H:i:00";
		$todaytime = $relativetime = false;
	}
	
	//reject or convert
	if (empty($timestamp) || ($timestamp == "Jan 1 1900 12:00AM")) return $error;
	if (!is_int($timestamp)) $timestamp = strtotime($timestamp);
	
	//get timestamp for today
	$todaysdate = mktime(0, 0, 1, $_josh["month"], $_josh["today"], $_josh["year"]);

	//get timestamp for argument, without time
	$returnday    = date("d", $timestamp);
	$returnyear   = date("Y", $timestamp);
	$returnmonth  = date("n", $timestamp);
	$returndate   = mktime(0, 0, 1, $returnmonth, $returnday, $returnyear);
	
	if ($relativetime) {
		//setup return date
		$datediff = ($returndate - $todaysdate) / 86400;
		if ($datediff == 0) {
			$return = ($todaytime) ? format_time($timestamp) : "Today";
		} elseif ($datediff == -1) {
			$return = "Yesterday";
		} elseif ($datediff == 1) {
			$return = "Tomorrow";
		} elseif (($datediff < -1) && ($datediff > -7)) { //last six days
			$return = date("l", $timestamp); //return day of week
		} else {
			$return = date($format, $timestamp);
		}
	} else {
		$return = date($format, $timestamp);
	}
	
	if ($return === 1) return $error;
	return $return;
}

function format_date_iso8601($timestamp=false) {
	//this looks like DATE_W3C http://www.php.net/manual/en/datetime.constants.php
	if (!$timestamp) $timestamp = time();
	if (!is_int($timestamp)) $timestamp = strtotime($timestamp);
	return date("Y-m-d", $timestamp) . "T" . date("H:i:s", $timestamp) . "-07:00";
}

function format_date_rss($timestamp=false) {
	if (!$timestamp) $timestamp = time();
	if (!is_int($timestamp)) $timestamp = strtotime($timestamp);
	return date(DATE_RSS, $timestamp);
}

function format_date_sql($month, $day, $year, $hour=0, $minute=0, $second=1) {
	global $_POST, $_josh;
	if (!empty($month) && !empty($day) && !empty($year)) {
		return "'" . date("Y-m-d H:i:00", mktime($hour, $minute, $second, $month, $day, $year)) . "'";
	} else {
		return "NULL";
	}
}

function format_date_time($timestamp, $error="", $separator="&nbsp;", $suppressMidnight=true) {
	$return = format_date($timestamp, $error, "M d, Y", false);
	//if (($return == "Today") || ($return == "Yesterday") || ($return == "Tomorrow")) 
	$time = format_time($timestamp);
	if ($suppressMidnight && ($time == "12:00am")) return $return;
	return $return . $separator . $time;;
}

function format_date_excel($timestamp) {
	if (!empty($timestamp)) return @date("n/j/Y", strtotime($timestamp));
}

function format_date_xml($timestamp=false) {
	if (!$timestamp) $timestamp = "now";
	if (!empty($timestamp) && $timestamp) return @date("Y-m-d", strtotime($timestamp)) . "T00:00:00.000";
}

function format_email($address) {
	//simple patch to prevent email form hijacking
	$address = strtolower(trim($address));
	$address = str_replace("'", "", $address);
	$address = str_replace('"', "", $address);
	$address = preg_replace("/\r/", "", $address);
	$address = preg_replace("/\n/", "", $address);
	
	if (!stristr($address, "@")) return false;
	if (!stristr($address, ".")) return false;	
	return $address;
}

function format_file_name($str, $ext) {
	$str = str_replace("  ",	" ",	$str);
	$str = str_replace("'",		"", 	$str);
	$str = str_replace(".",		"", 	$str);
	$str = str_replace(":",		"",		$str);
	$str = str_replace(" ",		"_",	$str);
	$str = substr($str, 0, 30);
	return strtolower($str . "." . $ext);
}  

function format_file_size($file) {
	$size = @filesize($file);
	return format_size($size);
} 

function format_highlight($haystack, $needles, $style="background-color:#FFFFBB;padding:1px;font-weight:bold;") {
	if (is_array($needles)) $needles = implode("|", $needles);
	return preg_replace("/($needles)/i","<span style='" . $style . "'><b>\\0</b></span>", $haystack);
}

function format_hilite($haystack, $needles, $style="background-color:#FFFFBB;padding:1px;font-weight:bold;") {
	//alias of format_highlight
	return format_highlight($haystack, $needles, $style);
}

function format_html($text) {
	/*clean up M$FT font crap -- OLD CODE
	$text = str_replace("font-size: 13pt;",					"", $text);
	$text = str_replace("color: #12253c;",					"", $text);
	$text = str_replace("font-family: Perpetua;",			"", $text);
	$text = str_replace("mso-bidi-font-family: Times;",		"", $text);
	$text = str_replace("font-size: 17px;",					"", $text);
	$text = str_replace("font-family: Times New Roman;",	"", $text);
	$text = str_replace("font-size: small;",				"",	$text);
	$text = str_replace('font-family: "',					'"', $text);
	$text = str_replace("<p><span>&nbsp;</span></p>",		"",	$text);
	$text = str_replace("<p>&nbsp;</p>",					"",	$text);
	$text = str_replace("font-size: medium;",				"",	$text);
	$text = str_replace("font-size: 16px;",					"",	$text);
	$text = str_replace("font-size: 12pt;",					"",	$text);
	$text = str_replace("font-size: 10pt;",					"",	$text);
	$text = str_replace("font-size: x-small;",				"",	$text);
	$text = str_replace(" class=MsoNormal",					"",	$text);
	$text = str_replace(' class="MsoNormal"',				"",	$text);
	$text = str_replace("font-family: Arial;",				"",	$text);
	*/

	$text = htmLawed($text, array('comment'=>1, 'cdata'=>1, 'style_pass'=>1));
	$text = str_replace("<p>&nbsp;</p>", "", $text);
	$text = str_replace(">&nbsp;</", "></", $text);
	
	return $text;
}

function format_html_text($str) {
	$return = strip_tags($str);
	$return = str_replace("&nbsp;", " ", $return);
	$return = trim($return);
	if (empty($return)) return false;
	return $return;
}

function format_image($path, $type=false) {
	global $_josh;
	//function to take any image and return JPG encoded binary.  could send to format image resize at that point
	//type should be used if you're sending a temp name (eg file upload)
	//requires the imagemagick convert unix command
	
	if (!$file = file_get($path)) return false;
	
	if (!$type) $type = file_ext($path);
	$target_name = DIRECTORY_WRITE . '/temp-target.jpg';
	
	$imagick = exec('which convert');
	if (empty($imagick)) $imagick = '/usr/local/bin/convert'; //not able to get correct path on my mac now
	
	if (($type == 'jpg') || ($type == 'jpeg')) {
		return $file;
	} elseif (($type == 'gif') || ($type == 'png')) {
		//convert
		$cmd = $imagick . ' ' . realpath($path) . ' ' . DIRECTORY_ROOT . $target_name;
		exec($cmd);
	} elseif ($type == 'pdf') {
		//return a screenshot of the first page
		exec($imagick . ' ' . realpath($path) . '[0] ' . DIRECTORY_ROOT . $target_name);
	} else {
		error_handle('unhandled image convert', __function__ . ' ran into a problem converting ' . $path, __file__, __line__);
		return false;
	}
		
	if ($source = file_get($target_name)) {
		file_delete($target_name);
		return $source;
	} else {
		error_handle('ImageMagick Not Installed', __function__ . ' requires the <a href="http://www.imagemagick.org/">ImageMagick PHP library</a> to work on the command line.  Please install it and try again.  ', __file__, __line__);
		return false;
	}
}

function format_image_resize($source, $max_width=false, $max_height=false) {
	if (!function_exists('imagecreatefromjpeg')) error_handle('library missing', 'the GD library needs to be installed to run format_image_resize', __file__, __line__);
	if (empty($source)) return null;

	if (!function_exists('resize')) {
		function resize($new_width, $new_height, $source_name, $target_name, $width, $height) {
			//resize an image and save to the $target_name
			$tmp = imagecreatetruecolor($new_width, $new_height);
			if (!$image = imagecreatefromjpeg(DIRECTORY_ROOT . $source_name)) error_handle('could not create image', 'the system could not create an image from ' . $source_name, __file__, __line__);
			imagecopyresampled($tmp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($tmp, DIRECTORY_ROOT . $target_name, 100);
			imagedestroy($tmp);
			imagedestroy($image);
		}

		function crop($new_width, $new_height, $target_name) {
			//crop an image and save to the $target_name
			list($width, $height) = getimagesize(DIRECTORY_ROOT . $target_name);

			//by default, crop from center
			$offsetx = ($width - $new_width) / 2;
			$offsety = ($height - $new_height) / 2;
			if ($offsetx < 0) $offsetx = 0;
			if ($offsety < 0) $offsety = 0;

			//this crops from top-left
			//$offsetx = $offsety = 0; 
			
			$tmp = imagecreatetruecolor($new_width, $new_height);
			if (!$image = @imagecreatefromjpeg(DIRECTORY_ROOT . $target_name)) error_handle('could not create image', 'the system could not create an image from ' . $source_name, __file__, __line__);
			imagecopyresized($tmp, $image, 0, 0, $offsetx, $offsety, $new_width, $new_height, $new_width, $new_height);
			imagejpeg($tmp, DIRECTORY_ROOT . $target_name, 100);
			imagedestroy($tmp);
			imagedestroy($image);
		}
	}

	//save to file, is file-based operation, unfortunately
	$source_name = DIRECTORY_WRITE . '/temp-source.jpg';
	$target_name = DIRECTORY_WRITE . '/temp-target.jpg';
	file_put($source_name, $source);

	//get source image dimensions
	list($width, $height) = getimagesize(DIRECTORY_ROOT . $source_name);
	
	if(!$width || !$height) {
		// image is probably corrupt
		echo draw_page('image corrupt', 'the uploaded image cannot be read, try opening the image in photo editing software, re-saving it, and then try again');
		exit();
	}
	
	//execute differently depending on target parameters	
	if ($max_width && $max_height) {
		//resizing both
		if (($width == $max_width) && ($height == $max_height)) {
			//already exact width and height, skip resizing
			copy(DIRECTORY_ROOT . $source_name, DIRECTORY_ROOT . $target_name);
		} else {
			//this was for the scenario where your target was a long landscape and you got a squarish image.
			//this doesn't work if your target is squarish and you get a long landscape
			//maybe we need a ratio function?  
			//square to long scenario: input 400 x 300 (actual 1.3 ratio), target 400 x 100 (target 4) need to resize width then crop target > actual
			//long to square scenario: input 400 x 100 (actual 4 ratio), target 400 x 300 (target 1.3) need to resize height then crop target < actual
			$target_ratio = $max_width / $max_height;
			$actual_ratio = $width / $height;
			//if ($max_width >= $max_height) {
			if ($target_ratio >= $actual_ratio) {
				//landscape or square.  resize width, then crop height
				$new_height = ($height / $width) * $max_width;
				resize($max_width, $new_height, $source_name, $target_name, $width, $height);
			} else {
				//portrait.  resize height, then crop width
				$new_width = ($width / $height) * $max_height;
				resize($new_width, $max_height, $source_name, $target_name, $width, $height);
			}
			crop($max_width, $max_height, $target_name);						
		}
	} elseif ($max_width) { 
		//only resizing width
		if ($width == $max_width) {
			//already exact width, skip resizing
			copy(DIRECTORY_ROOT . $source_name, DIRECTORY_ROOT . $target_name);
		} else {
			//resize width
			$new_height = ($height / $width) * $max_width;
			resize($max_width, $new_height, $source_name, $target_name, $width, $height);

		}
	} elseif ($max_height) { 
		//only resizing height	
		if ($height == $max_height) {
			//already exact height, skip resizing
			copy(DIRECTORY_ROOT . $source_name, DIRECTORY_ROOT . $target_name);
		} else {
			//resize height
			$new_width = ($width / $height) * $max_height;
			resize($new_width, $max_height, $source_name, $target_name, $width, $height);
		}
	}
	$return = file_get($target_name);
	
	//clean up
	file_delete($source_name);
	file_delete($target_name);
	
	return $return;
}

function format_js_desanitize() {
	//javascript function for decoding sanitized strings
	return '
	function desanitize(string) {
		return string.replace(/replacedash/g, "-").replace(/replaceslash/g, "/").replace(/replacespace/g, " ").substring(1);
	}
	';
}

function format_js_sanitize($string) {
	//return javascript-sanitized key
	//need for rollover script for seedco financial and phoebe murer
	$string = "a" . $string; //doesn't like variables that start with numbers
	$string = str_replace("-", "replacedash", $string); //or contain dashes
	$string = str_replace("/", "replaceslash", $string); //or contain slashes
	$string = str_replace(" ", "replacespace", $string); //or contain spaces
	return $string;
}

function format_money($value, $dollarsign=true, $comma=true, $error="") {
	$negative = ($value < 0);
	$value = format_num($value, 2, $comma, $error);
	if ($value == $error) return $value;
	if ($dollarsign) {
		if ($negative) {
			$value = "-$" . str_replace("-", "", $value);
		} else {
			$value = "$" . $value;
		}
	}
	return $value;
}

function format_null($value="") {
	//could also be a db function?
	if (empty($value)) return "NULL";
	if (!is_numeric($value)) return "'" . $value . "'";
	return $value;
}

function format_num($value, $decimals=false, $comma=true, $error="n/a") {
	//output function
	if (empty($value)) return $error;
	if (!format_verify($value, "num")) return $error;
	if ($comma) $comma = ",";
	return number_format($value, $decimals, ".", $comma);
}

function format_numeric($value) {
	//input function
	$characters = "0123456789.";
	$newval = "";
	for ($i = 0; $i < strlen($value); $i++) if (strpos($characters, $value[$i]) !== false) $newval .= $value[$i];
	if (empty($newval)) {
		error_debug("<b>format_numeric</b> received $value and is sending back false");
		return false;
	} else {
		error_debug("<b>format_numeric</b> received $value and is sending back " . $newval);
		return $newval - 0;
	}
}

function format_percentage($float, $precision=2) {
	return round($float * 100, $precision) . "%";
}

function format_phone($string, $fail=false) { //format a phone number to (123) 456-7890 format
	$number = "";
	for ($i = 0; $i < strlen($string); $i++) if (is_numeric($string[$i])) $number .= $string[$i];
	if ((strlen($number) != 10) || ($number == "9999999999")) {
		if ($fail) return false;
		return $string;
	}
	return "(" . substr($number, 0, 3) . ") " . substr($number, 3, 3) . "-" . substr($number, 6, 4);
}

function format_pluralize($entity) {
	$length = strlen($entity);
	if (substr($entity, -1) == "y") {
		return substr($entity, 0, ($length - 1)) . "ies";
	} else {
		return $entity . "s";
	}
}

function format_post_bits($fieldnames) {
	//takes a comma-separated list of POST keys (checkboxes) and sets bit values in their places
	$fields = array_post_fields($fieldnames);
	foreach ($fields as $field) $_POST[$field] = (!empty($_POST[$field])) ? 1 : 0;
}

function format_post_date($str) {
	global $_POST;
	
	$month  = $_POST[$str . "Month"];
	$day    = $_POST[$str . "Day"];
	$year   = $_POST[$str . "Year"];
	
	$hour   = isset($_POST[$str . "Hour"])   ? $_POST[$str . "Hour"]   : 0;
	$minute = isset($_POST[$str . "Minute"]) ? $_POST[$str . "Minute"] : 0;
	$second = isset($_POST[$str . "Second"]) ? $_POST[$str . "Second"] : 0;
	
	if (isset($_POST[$str . "AMPM"])) {
		if ($_POST[$str . "AMPM"] == "AM") {
			if ($hour == 12) $hour = 0;
		} else {
			if ($hour != 12) $hour +=12;
		}
	}
	error_debug("<b>format_post_date</b> for $str into mdyhms: $month, $day, $year, $hour, $minute, $second");
	return format_date_sql($month, $day, $year, $hour, $minute, $second);
}

function format_post_float($fieldnames) {
	//takes a comma-separated list of POST keys and replaces them with monetary values or NULLs if they're empty
	global $_POST;
	$fields = array_post_fields($fieldnames);
	foreach ($fields as $field) {
		$_POST[$field] = format_numeric($_POST[$field]);
		if ($_POST[$field] === false) $_POST[$field] = "NULL";
	}
}

function format_post_html($fieldnames) {
	//takes a comma-separated list of POST keys and formats the html in them
	global $_POST;
	$fields = array_post_fields($fieldnames);
	foreach ($fields as $field) {
		$return = format_html($_POST[$field]);
		$_POST[$field] = (empty($return)) ? "NULL" : "'" . $_POST[$field] . "'";
	}
}

function format_post_nulls($fieldnames) {
	//takes a comma-separated list of POST keys and replaces them with NULLs if they're empty
	global $_POST;
	error_debug("<b>format_post_nulls</b> for " . $fieldnames);
	$fields = array_post_fields($fieldnames);
	foreach ($fields as $field) {
		if (!strlen($_POST[$field])) {
			error_debug("<b>format_post_nulls</b> nullifying " . $field);
			$_POST[$field] = "NULL";
		}
	}
}

function format_post_urls($fieldnames) {
	//takes a comma-separated list of POST keys and formats them as NULLs or urls
	global $_POST;
	error_debug("<b>format_post_urls</b> for " . $fieldnames);
	$fields = array_post_fields($fieldnames);
	foreach ($fields as $field) $_POST[$field] = format_null(format_url($_POST[$field]));
}

function format_q($quantity, $entity, $capitalize=true) {
	//alias for format_quantitize
	return format_quantitize($quantity, $entity, $capitalize);
}

function format_quantitize($quantity, $entity, $capitalize=true) {
	global $_josh;
	if ($quantity == 0) {
		$return = "no " . format_pluralize($entity);
	} elseif ($quantity == 1) {
		$return = "one " . $entity;
	} elseif (is_integer($quantity) && ($quantity < 10)) {
		$return = $_josh["numbers"][$quantity] . " " . format_pluralize($entity);
	} else {
		$return = $quantity . " " . format_pluralize($entity);
	}
	if ($capitalize) $return = format_title($return);
	return $return;
}

function format_quotes($value) {
	if (format_verify($value, "string")) $value = trim(str_replace("'", "''", stripslashes($value)));
	return $value;
}

function format_size($size) {
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		$size /= 1024;
		$pos++;
	}
	return round($size) . " " . $a[$pos];
}

function format_ssn($str) {
	return substr($str, 0, 3) . "-" . substr($str, 3, 2) . "-" . substr($str, 5, 4);
}

function format_string($str, $len=30, $tail="&hellip;") {
	if (strlen($str) > $len) {
		return substr($str, 0, ($len - 3)) . $tail;
	} else {
		return $str;
	}
}

function format_text_code($str) {
	$return = strtolower($str);
	$return = str_replace("/",	"_",	$return);
	$return = str_replace(" ",	"_",	$return);
	$return = str_replace("&",	"and",	$return);
	return $return;
}

function format_text_ends($needle, $haystack) {
	if (strtolower(substr($haystack, (0 - strlen($needle)))) == strtolower($needle)) return true;
	return false;
}

function format_text_human($str, $convertdashes=true) {
	$return = str_replace("_", " ", strtolower($str));
	if ($convertdashes) $return = str_replace("-", " ", $return);
	return format_title($return);
}

function format_text_shorten($text, $length=30, $append="&#8230;", $appendlength=1) {
	if ($append) $length = $length - $appendlength;
	if (strlen($text) > $length) return substr($text, 0, $length) . $append;
	return $text;
}

function format_text_starts($needle, $haystack) {
	if (strtolower(substr($haystack, 0, strlen($needle))) == strtolower($needle)) return true;
	return false;
}

function format_time($timestamp=false, $error="") {
	if ($timestamp === false) {
		$timestamp = time();
	} else {
		if (empty($timestamp) || ($timestamp == "Jan 1 1900 12:00AM")) return $error;
		if (!is_int($timestamp)) $timestamp = strtotime($timestamp);
	}
	return date("g:ia", $timestamp);
}

function format_time_business($start, $end=false) {
	$start = strtotime($start);
	if (empty($end) || !$end) {
		$end = date("U");
	} else {
		$end = strtotime($end);
	}
	$age = $end - $start;
	
	//days
	if ($age > 86400) {
		$days = 0;
		$finished = $start + 86400;
		for ($i = $finished; $i < $end; $i += 86400) {
			if (isWeekDay($i)) $days++;
			$finished = $i;
		}
		if ($days == 1) {
			$return[] = $days . " day";
		} elseif ($days > 1) {
			$return[] = $days . " days";
		}
	} else {
		$finished = $start;
	}

	//hours
	$hours = 0;
	for ($i = $finished + 3600; $i < $end; $i += 3600) {
		if (isBusinessHours($i)) $hours++;
		$finished = $i;
	}
	if ($hours == 1) {
		$return[] = $hours . " hour";
	} elseif ($hours > 1) {
		$return[] = $hours . " hours";
	}

	//minutes
	$minutes = round(($age % 3600) / 60);
	if ($minutes == 1) {
		$return[] = $minutes . " minute";
	} elseif ($minutes > 1) {
		$return[] = $minutes . " minutes";
	}
	
	if (empty($return)) {
		return "<i>just opened</i>";
	} else {
		return implode(", ", $return);
	}
}

//don't know how to categorize these - they only belong to the function above
function isBusinessHours($udate) {
	$hourOfDay = date("G", $udate);
	//echo format_date($udate, true, " ");
	return (($hourOfDay > 9) && ($hourOfDay < 17)) ? true : false;
}

function isWeekDay($udate) {
	$dayOfWeek = date("w", $udate);
	return (($dayOfWeek > 0) && ($dayOfWeek < 6)) ? true : false;
}
	

function format_time_exec($start_time=false, $descriptor=" seconds") {
	if (!$start_time) {
		global $_josh;
		if (isset($_josh["time_start"])) $start_time = $_josh["time_start"];
	}
	return round(microtime(true) - $start_time, 2) . $descriptor;
}

function format_times($num) {
	global $_josh;
	if ($num == 1) {
		return "once";
	} elseif ($num == 2) {
		return "twice";
	} elseif ($num < 10) {
		return $_josh["numbers"][$num] . " times";
	} else {
		return number_format($num) . " times";
	}
}

function format_title($str) {
	error_debug("<b>format_title</b> starting with " . $str);
	$return = array();
	$lower = array("a", "an", "and", "but", "for", "from", "if", "in", "nor", "of", "on", "or", "so", "the", "to", "via", "with");
	$mixed = array("iPhone", "iPhones", "IPs", "SSNs", "TinyMCE", "URLs", "WordPress");
	$upper = array("ADA", "ASAP", "BIF", "CCT", "CSS", "DB", "EBO", "FSS", "FTP", "HTML", "IP", "NHP", "NVN", "OMG", "ONYC", "OS", "PC", "PHP", "PLC", "RSS", "SF", "SFS","SQL", "SSN", "URL", "WTF", "XML");
	$words = explode(" ", ucwords(strtolower(trim($str))));
	$counter = 1;
	$max = count($words);
	foreach ($words as $word) {
		if (in_array(strtolower($word), $lower) && ($counter != 1) && ($counter != $max)) {
			$return[] = strtolower($word);
		} elseif (in_array(strToUpper($word), $upper)) {
			$return[] = strToUpper($word);
		} elseif (!empty($word)) {
			$index = array_search(strtolower($word), array_to_lower($mixed));
			if ($index !== false) { //could return 0, which would be valid
				$return[] = $mixed[$index];
			} else {
				$return[] = $word;
			}
		}
		$counter++;
	}
	return implode(" ", $return);
}

function format_url($str="") {
	if (empty($str)) return false;
	if (format_text_starts("http://", $str) || format_text_starts("https://", $str)) return $str;
	return "http://" . $str;
}

function format_verify($variable, $type="int") {
	error_debug("<b>format_verify</b> for " . $variable);
	if ($type == "int") {
		if (!is_numeric($variable)) {
			$variable += 0;
			if (!is_int($variable)) return false;
		}
	} elseif ($type == "num") {
		if (!is_numeric($variable)) return false;
	} elseif ($type == "key") {
		if (strlen($variable) > 13) return false;
	} elseif ($type == "string") {
		if (!is_string($variable)) return false;
	}
	return true;
}

function format_zip($string, $error=false) { //format a ZIP (5-digit)
	$number = "";
	for ($i = 0; $i < strlen($string); $i++) if (is_numeric($string[$i])) $number .= $string[$i];
	if (strlen($number) >= 5) return substr($number, 0, 5);
	return $error;
}


?>