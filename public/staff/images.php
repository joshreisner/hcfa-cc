<?php
/*
update intranet_users set bio = null where bio = '<p>&nbsp;</p>';
update intranet_users set bio = null where bio = '<p> </p>';
update intranet_users set bio = null where bio = '<p>Â </p>';
*/

include('../include.php');

$images = db_table('SELECT 
		u.userID,
		i.width,
		i.height,
		i.image,
		t.extension
	FROM intranet_users u
	JOIN intranet_images_backup i ON u.imageIDbackup = i.imageID
	JOIN intranet_doctypes t ON i.docTypeID = t.id');

define('DIRECTORY_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('DIRECTORY_WRITE', '/uploads');

foreach ($images as $image) {
	$temp = '../uploads/temp.' . $image['extension'];
	$target = '../uploads/staff/' . $image['userID'] . '.jpg';
	
	file_put_contents($temp, $image['image']);
	$content = format_image($temp, $image['extension']);
	$content = format_image_resize($content, 320, 320);
	file_put_contents($target, $content);
	unlink($temp);
	
	echo $target . '<br>';
}