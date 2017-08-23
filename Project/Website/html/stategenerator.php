<?php
	require_once 'databaseinfo.php';
	
	// Check if Canada or U.S.
	$country = $_REQUEST['country'];
	
	// We want to find all states/provinces in that country
	$query = "SELECT state_id, state_name FROM states WHERE country_id = '$country' ORDER BY state_name";
	$result = $conn->query($query);
	$rows = $result->num_rows;
	
	$return_text = '';
	
	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		
		// Ontario should be the default option
		$return_text .= ($row[0] == 'ON') ? "<option value='ON' selected>Ontario</option>" : "<option value='$row[0]'>$row[1]</option>";
	}
	
	echo $return_text;
?>