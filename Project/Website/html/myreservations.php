<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'myreservations.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
	}
	
	// You need to be logged in to access this page
	if (!$_SESSION['login']) {
		header("Location: login.php");
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <!-- This website uses the Bootstrap default webpage layout as a starting point -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Vacation Inn - Sign Up</title>

    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
	
	<!-- My own CSS -->
	<link href="../css/styles.css" rel="stylesheet">
	
	<!-- "Fira Sans" font from Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Fira+Sans:600,900" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  
  <?php
  
    $output = '';
	
	$fname = '';
	$lname = '';
	$address = '';
	$email = '';
	$city = '';
	$country = '';
	$state = '';

	
	$customer_id = $_SESSION['customer_id'];
	
	// To auto-fill the update form with previously submitted data
	$query = "SELECT first_name, last_name, street_address, city, country_id, state_id, email FROM customers WHERE customer_id = $customer_id";
	$result = $conn->query($query);
	$rows = $result->num_rows;
	
	if (!$result) {
		die("Database access failed: " . $conn->error);
	}
	
	elseif ($rows !== 1) {
		$output .= "There is a serious database error. Please contact a staff member for help.<br>";
	}
	
	else {
		$result->data_seek(0);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		
		$fname = $row['first_name'];
		$lname = $row['last_name'];
		$address = $row['street_address'];
		$city = $row['city'];
		$country = $row['country_id'];
		$state = ($row['state_id']) ? $row['state_id'] : '';
		$email = $row['email'];
	}
	
	// This section processes the form once submitted:
	if (isset($_POST['update-account'])) {
		
		// First, check all fields are set, and 'sanitize' them:
		$fname = $conn->real_escape_string($_POST['fname']);
		$lname = $conn->real_escape_string($_POST['lname']);
		$address = $conn->real_escape_string($_POST['address']);
		$city = $conn->real_escape_string($_POST['city']);
		$country = $conn->real_escape_string($_POST['country']);
		$state = ($_POST['state']) ? $conn->real_escape_string($_POST['state']) : ''; // State may be empty, so we should check for this to be safe
		$email = $conn->real_escape_string($_POST['email']);
		$password = $conn->real_escape_string(hash('ripemd128', $pre_salt . $_POST['password'] . $post_salt));

		
		if (!$fname) {
			$output .= 'Please enter a first name.<br>';
		}
		if (!$lname) {
			$output .= 'Please enter a last name.<br>';
		}
		if (!$address) {
			$output .= 'Please enter a street address.<br>';
		}
		if (!$city) {
			$output .= 'Please enter a city.<br>';
		}
		
		// It is impossible to fill out the form without selecting country and state (if applicable), so no need to check if those are missing
		
		if (!$email) {
			$output .= 'Please enter an email address.<br>';
		}

		// Check for email uniqueness
		else {
			$query = "SELECT * FROM customers WHERE email = '$email'";
			
			$result = $conn->query($query);
					
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			// This shouldn't ever happen since email should be unique in the database
			if ($rows > 1) {
				$output .= "A serious database error has occurred. Please contact a site administrator for help.<br>";
			}
			
			elseif ($rows === 1) {
				
				// The match could just be the user's previous email, which is OK, so we need to check first
				$result->data_seek(0);
				$row = $result->fetch_array(MYSQLI_ASSOC);
				$matching_email = $row['email'];
				
				// If not a match to their previous email, then not OK
				if ($matching_email !== $email) {
					$output .= "The email address you entered is already registered to another user. Please enter a different email address.<br>";
				}
			}
		}		
		
		
		// Password must have at least 5 characters
		if (!$_POST['password'] || strlen($_POST['password']) < 5) {
			$output .= 'Please enter a password that has at least 5 characters.<br>';
		}
		
		// If $output is still empty, the form was submitted correctly
		if (strlen($output) === 0) {
			$query = "UPDATE customers SET first_name = ?, last_name = ?, street_address = ?, city = ?, country_id = ?, ";
			$query .= "state_id = ?, ";
			$query .= "email = ?, password = ? WHERE customer_id = ?";
			
			$stmt = $conn->prepare($query);
			$stmt->bind_param('ssssssssi', $fname, $lname, $address, $city, $country, (($state !== '') ? $state : null), $email, $password, $customer_id);
			
			if ($stmt->execute()) {
				$output .= "<span class='green'>Records updated successfully!</span><br>";
			}
			else {
				$output .= "Error updating data: " . $conn->error . "<br>";
			}
		}
		
		
	}
	
	elseif (isset($_POST['delete-rooms'])) {
		
		$query = "DELETE FROM reservations WHERE reservation_id = ?";
		$stmt = $conn->prepare($query);
		
		$reservations = $_POST['reservations'];
		
		if (count($reservations) === 0) {
			$output .= "<span class='red'>You haven't selected any reservations to delete!</span>";
		}
		
		foreach ($reservations as $reservation) {
			
			$stmt->bind_param('i', $reservation);
			
			if(!$stmt->execute()) {
				die("Error deleting reservation: " . $conn->error);
			}
		}
		
		if (strlen($output) === 0) {
			$output .= count($reservations);
			$output .= (count($reservations) === 1) ? ' reservation deleted successfully!<br>' : ' reservations deleted successfully!<br>';
		}
	}
	
  ?>
 
    <!-- HEADER -->
	<div class="header">
		<div class="header-img">
			<img class="logo" src="../pictures/Logomakr_8eWW9h.png" alt="Vacation Inn">
		</div>
		<div class="header-slogan">
			Your home away from home!
		</div>
	</div>
	
	<!-- NAVBAR: Content adapted from W3Schools -->
	<nav class="navbar navbar-inverse">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Site Navigation</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Home</a></li>
					<li><a href="rooms.php">Rooms</a></li>
					<li class="active"><a href="myreservations.php">Account and Reservations</a></li> 
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php
					
					if (!$_SESSION['login']) {
						echo '<li class="active"><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>';
						echo '<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Log In</a></li>';
					} else {
						echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>';
					}
					
					?>
				</ul>
			</div>
		</div>
	</nav>
	
	
	<!-- BODY -->
	<div class="main">
	<?php
	
	echo <<<_END
		<h1 class="bold text-center">Update Account</h1>
		<p class="text-center">If you wish to update your account, please complete the following form and submit.</p>
		<p id="error-message" class="bold red">
_END;
		if (isset($_POST['update-account'])) {
			echo $output;
			unset($_POST['update-account']);
		}
		echo <<<_END
		</p>
		
		<!-- Form adapted from W3Schools -->
		<form class="form-horizontal form-render" action="myreservations.php" method="post" id="customer-update">
			<div class="form-group">
				<label class="control-label col-sm-2" for="fname">First Name:</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="fname" name="fname" placeholder="Enter first name" value="$fname">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="lname">Last Name:</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lname" name="lname" placeholder="Enter last name" value="$lname">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="address">Street Address:</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="address" name="address" placeholder="Enter street address" value="$address">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="city">City:</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="city" name="city" placeholder="Enter city" value="$city">
				</div>
			</div>
			<div class="form-group">
				<label id="country-label" class="control-label col-sm-2" for="country">Country:</label>
				<div class="col-sm-10">
				<select class="form-control" id="country" name="country">
_END;
				// Use PHP to populate select list with countries
				$query = "SELECT country_id, country_name FROM countries ORDER BY country_name";
				$result = $conn->query($query);
				
				if (!$result) {
					die ("Database access failed: " . $conn->error);
				}
				
				$rows = $result->num_rows;
				
				for ($i = 0; $i < $rows; ++$i) {
					$result->data_seek($i);
					$row = $result->fetch_array(MYSQLI_NUM);
					
					// Select user's default country by default
					if ($row[0] == $country) {
						echo "<option value='$row[0]' selected>$row[1]</option>";
					} else {
						echo "<option value='$row[0]'>$row[1]</option>";
					}
				}
			
			echo <<<_END
				</select>
				</div>
			</div>
			<div class="form-group">
				<label id="state-label" class="control-label col-sm-2" for="state">Province:</label>
				<div class="col-sm-10">
					<select class="form-control" id="state" name="state">
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="email">Email:</label>
				<div class="col-sm-10">
					<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" value="$email">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="password">Password:</label>
				<div class="col-sm-10"> 
					<input type="password" class="form-control" id="password" name="password" placeholder="Enter new password (at least 5 characters)">
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-12 text-center">
					<button type="submit" class="btn btn-default" name="update-account" id="update-account">Update Account</button>
				</div>
			</div>
		</form>
_END;
	
	?>
	
	<h1 class="bold text-center">View My Reservations</h1>
	<p class="text-center">You can view your past and future reservations here, and delete any future reservations that you have.</p>
	<p class="bold green" id="delete-message">
	<?php
	if (isset($_POST['delete-rooms'])) {
		echo $output;
		unset($_POST['delete-rooms']);
	}
	?>
	</p>
	
	<form id="room-selections" method="post" action="myreservations.php">
		<table class="table table-hover table-bordered" id="reservation-table">
			<thead>
				<tr>
					<th>Room</th>
					<th class="vanish">Type</th>
					<th>Check-In</th>
					<th class="vanish">Check-Out</th>
					<th>Cost</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			
			<?php
			
			$query = "SELECT room_number, room_type_description, start_date, end_date, price, reservation_id FROM reservations ";
			$query .= "NATURAL JOIN rooms NATURAL JOIN room_types WHERE customer_id = $customer_id ORDER BY start_date";
			$result = $conn->query($query);
			
			if (!$result) {
				die("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			for ($i = 0; $i < $rows; ++$i) {
				
				echo "<tr>";
				
				$result->data_seek($i);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				for ($j = 0; $j < 5; ++$j) {
					
					// Display price in correct format
					// Code to find difference in dates in PHP is found here: https://stackoverflow.com/questions/2040560/finding-the-number-of-days-between-two-dates
					if ($j === 4) {
						
						// Find day difference
						$start = new DateTime($row[2]);
						$end = new DateTime($row[3]);
						$diff = $start->diff($end);
						$nights = (int) $diff->format('%a');
						
						// Calculate cost including tax:
						$final = number_format($row[4] * $nights * 1.13, 2);
						echo "<td>$$final</td>";
					}
					
					// The odd-numbered columns (except 'delete') aren't important on mobile devices, so render with class='vanish'
					elseif ($j % 2 === 1) {
						echo "<td class='vanish'>$row[$j]</td>";
					}
					
					else {
						echo "<td>$row[$j]</td>";
					}
				}
				
				// Echo delete checkbox, but only if start date is in the future:
				$now = time();
				$start = strtotime($row[2]);
				echo ($start - $now > 0) ? "<td><input type='checkbox' name='reservations[]' value='$row[5]'></td></tr>" : "<td></td></tr>";
			}
			
			?>
				
			</tbody>
		</table>
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="delete-rooms" id="delete-rooms">Delete Selected Reservations</button>
			</div>
		</div>
		
	</form>
	
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	<script src="../js/myreservations.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->$close();
  ?>
</html>