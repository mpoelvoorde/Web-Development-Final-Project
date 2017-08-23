<?php

require_once 'databaseinfo.php';

$roomtype = $_REQUEST['roomtype'];
$output = '';

$query = "SELECT description FROM room_types WHERE room_type = '$roomtype'";
$result = $conn->query($query);

if (!$result) {
	die("Database access failed: " . $conn->error);
}

$rows = $result->num_rows;

if ($rows !== 1) {
	$output .= "Our database has a serious error. Contact a site administrator.";
}

else {
	$result->data_seek($i);
	$row = $result->fetch_array(MYSQLI_NUM);

	$output .= $row[0];
}

echo $output;

?>