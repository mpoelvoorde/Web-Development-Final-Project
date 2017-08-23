<?php

	require_once 'databaseinfo.php';
	
	$start_date = $_REQUEST['date'];
	$nights = $_REQUEST['nights'];
	$beds = $_REQUEST['beds'];
	$order = $_REQUEST['order'];
	
	// Calculate end date of reservation.
	// Learned this from https://stackoverflow.com/questions/1394791/adding-one-day-to-a-date
	$end_date = date('Y-m-d', strtotime("$start_date +$nights day"));
	
	$output = '';
	
	// $output = "'$start_date' end: '$end_date' nights: '$nights'";
	
	
	/* First, I need to determine which rooms are available for those nights.
	I need to determine if any reservations have an end date LATER than this start date, AND also check
	for any reservations that start BEFORE the anticipated end date of the reservation.
	If a room violates either condition, it's not available for rent to this customer.
	
	NOTE: If a reservation has an end date that is the same as another's start date, that
	is NOT a conflict, since the first guest leaves in the morning and the second arrives
	later that evening.
	
	To do this, I will iterate through every day from start_date to end_date, checking for the existence
	of any reservations that start or end on that date. These rooms are added to an excluded list, and
	only the rooms not on the excluded list are displayed to the user.
	*/
	
	/*
	For future personal reference:
	$query = "SELECT room_number, room_type_description, beds, description
				FROM room_types NATURAL JOIN rooms NATURAL JOIN reservations
				WHERE room_number...
	*/
	
	$excluded_rooms = array();
	
	// First, check for day 1 (ending ON the start date is not a conflict):
	$query = "SELECT room_number FROM reservations WHERE start_date <= '$start_date' AND end_date > '$start_date'";
	$result = $conn->query($query);
	
	if (!$result) {
		die("Database access failed: " . $conn->error);
	}
	
	$rows = $result->num_rows;
	
	for ($i = 0; $i < $rows; ++$i) {		
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		$excluded_rooms[] = $row[0];
	}
	
	$result->close();
	
	// Days 2 to (end date - 1), where end and start dates are a conflict
	for ($i = date('Y-m-d', strtotime("$start_date +1 day")); $i != $end_date; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		
		$query = "SELECT room_number FROM reservations WHERE start_date <= '$i' AND end_date >= '$i'";		
		
		$result = $conn->query($query);
		
		if (!$result) {
			die("Database access failed: " . $conn->error);
		}
		
		$rows = $result->num_rows;
		
		for ($j = 0; $j < $rows; ++$j) {		
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
			
			// Add room if not already in array
			if (!in_array($row[0], $excluded_rooms)) {
				$excluded_rooms[] = $row[0];
			}
		}
	}
	
	// End date (end date on another start date is NOT a conflict)
	$query = "SELECT room_number FROM reservations WHERE start_date < '$end_date' AND end_date >= '$end_date'";
	$result = $conn->query($query);
	
	if (!$result) {
		die("Database access failed: " . $conn->error);
	}
	
	$rows = $result->num_rows;
	
	for ($i = 0; $i < $rows; ++$i) {		
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		
		// Add room if not already in array
		if (!in_array($row[0], $excluded_rooms)) {
			$excluded_rooms[] = $row[0];
		}
	}
	
	// Now, to do the query that shows every room available
	$query = "SELECT room_number, base_price, room_type, room_type_description, beds, description
				FROM room_types NATURAL JOIN rooms";
				
	$excluded_count = count($excluded_rooms);
	
	if ($excluded_count > 0) {
		$query .= " WHERE ";
	}
	
	for ($i = $excluded_count - 1; $i > 0; --$i) {
		$query .= "room_number != " . $excluded_rooms[$i] . " AND ";
	}
	
	if ($excluded_count > 0) {
		$query .= "room_number != " . $excluded_rooms[0];
	}
	
	// Now to filter out the results by number of beds selected...
	if ($beds > 0) {
		$query .= ($excluded_count > 0) ? " AND beds = $beds" : " WHERE beds = $beds";
	}
	
	// And also order by the appropriate parameter:
	switch ($order) {
		case 0:
			$query .= " ORDER BY room_number";
			break;
		case 1:
			$query .= " ORDER BY base_price";
			break;
		case 2:
			$query .= " ORDER BY base_price DESC";
			break;
	}
	
	$result = $conn->query($query);
	
	if (!$result) {
		die("Database access failed: " . $conn->error);
	}
	
	$rows = $result->num_rows;
	
	$output .= ($rows === 0) ? '' : "<form method='post' action='reserve.php'><div class='row'>";
	
	
	// TODO: Display output in $output
	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		$output .= <<<_END
		<div class="col-sm-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="panel-title"><div class="left-align">Room $row[0]</div><div class="right-align">$$row[1]</div></div>
				</div>
				<div class="panel-body">
_END;

					switch ($row[2]) {
						case 'b':
							$output .= "<img src='../pictures/standard-room.jpg' alt='Standard Room' class='room-image'>";
							$output .= "<p class='bold'>$row[3]</p><p>Beds: $row[4]</p><p class='clear-left'>$row[5]</p>";
							$output .= <<<_END
							<div class="checkbox">
								<input type="checkbox" id="room$row[0]" class="checkbox" name="rooms[]" value="$row[0]">
								<label for="room$row[0]">
									<span class="bold">Select this room</span>
								</label>
							</div>
_END;
							break;
						case 'd':
							$output .= "<img src='../pictures/double-bedroom.jpg' alt='Standard Room' class='room-image'>";
							$output .= "<p class='bold'>$row[3]</p><p>Beds: $row[4]</p><p class='clear-left'>$row[5]</p>";
							$output .= <<<_END
							<div class="checkbox">
								<input type="checkbox" id="room$row[0]" class="checkbox" name="rooms[]" value="$row[0]">
								<label for="room$row[0]">
									<span class="bold">Select this room</span>
								</label>
							</div>
_END;
							break;
						case 's':
							$output .= "<img src='../pictures/master-bedroom.jpg' alt='Standard Room' class='room-image'>";
							$output .= "<p class='bold'>$row[3]</p><p>Beds: $row[4]</p><p class='clear-left'>$row[5]</p>";
							$output .= <<<_END
							<div class="checkbox">
								<input type="checkbox" id="room$row[0]" class="checkbox" name="rooms[]" value="$row[0]">
								<label for="room$row[0]">
									<span class="bold">Select this room</span>
								</label>
							</div>
_END;
							break;
					}

		$output .= <<<_END
				</div>
			</div>
		</div>
_END;
		
		// A "row" ends on odd numbered entries
		if ($i % 2 == 1) {
			$output .= "</div><div class='row'>";
		}
		
		// If $i = $rows - 1 (last iteration), the row also ends
		if ($i == $rows - 1) {
			$output .= "</div>";
		}
	}
	
	
	if (strlen($output) == 0) {
		$output = "<h3 class='text-center bold'>There are no rooms available</h3>";
	}
	
	// TODO: Return start date and end date as "hidden" form elements
	$output .= "<input type='hidden' name='start_date' value='$start_date'><input type='hidden' name='end_date' value='$end_date'>";
	$output .= "<input type='hidden' name='nights' value='$nights'>";
	$output .= ($rows === 0) ? '' : "<p class='text-center'><button type='submit' class='btn btn-success' id='confirm' name='confirm'>Confirm Reservations</button></p></form>";
	
	echo $output;
?>