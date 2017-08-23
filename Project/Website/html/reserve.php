<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'rooms.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
	}
	
	// User needs to be redirected to login if not logged in:
	// (The user shouldn't be able to reach this page without logging in anyway)
	if (!$_SESSION['login']) {
		header("Location: login.php");
	}
	
	$error_message = '';
	
	// If user didn't select a room
	if (!isset($_POST['rooms']) || empty($_POST['rooms'])) {
		$error_message .= 'Error: No rooms selected. Please select a <a href="rooms.php">room</a> to continue.<br>';
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
    <title>Vacation Inn - Payment Form</title>

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
	
	// Fake initializations so these variables are in scope at this level
	$result = '';
	$query = '';
	$rows = 0;
	$row = array();
	$rooms = $_POST['rooms'];
	$room_types = array();
	$prices = array();
	$nights = $_POST['nights'];
	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	
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
					<li><a href="myreservations.php">Account and Reservations</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php
					
					if (!$_SESSION['login']) {
						echo '<li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>';
						echo '<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Log In</a></li>';
					} else {
						echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>';
					}
					
					?>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="main">
		<h1 class="bold text-center">Confirm Reservation</h1>
		
		<?php
		
		if ($error_message) {
			echo "<p id='error-message' class='bold red'>$error_message</p>";
		}
		
		else {
		
		?>
			
			<!-- Table to show the selected rooms. Code adapted from W3Schools -->
			<?php
			
			// Store room prices as array
			foreach ($rooms as $room) {
				$query = "SELECT base_price, room_type_description FROM rooms NATURAL JOIN room_types WHERE room_number = $room";
				
				$result = $conn->query($query);
				$rows = $result->num_rows;
				
				if ($rows !== 1) {
					echo "<p class='red bold'>A serious error has occurred</p>";
					return;
				}
				
				$result->data_seek(0);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				$prices[] = $row[0];
				$room_types[] = $row[1];
			}
			
			?>
			<div id="room-selections">
				<h3>Selected Rooms and Dates</h3>
				<p><span class="bold">Check-In Date:</span> <?php echo date('l, F j, Y', strtotime($start_date)); ?></p> 
				<p><span class="bold">Check-Out Date:</span> <?php echo date('l, F j, Y', strtotime($end_date)); ?></p>
				
				
				<table class="table table-hover table-bordered">
					<thead>
						<tr>
							<th>Room</th>
							<th>Room Type</th>
							<th>Per Night</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						<?php
						
						$count = count($rooms);
						for ($i = 0; $i < $count; ++$i) {
							echo <<<_END
								<tr>
									<td>$rooms[$i]</td>
									<td>$room_types[$i]</td>
_END;
							echo "<td>$" . number_format($prices[$i], 2) . "</td>";
							echo "<td>$" . number_format($prices[$i] * $nights, 2) . "</td>";
						}
						
						// Calculate Subtotal and Taxes:
						$subtotal = 0;
						foreach ($prices as $x) {
							$subtotal += $x;
						}
						
						$subtotal *= $nights;
						
						$tax = $subtotal * .13;
						
						$total_cost = $subtotal + $tax;
						
						?>
						<tr>
							<td class="align-right" colspan="3">Subtotal:</td>
							<td>$<?php echo number_format($subtotal, 2); ?></td>
						</tr>
						<tr>
							<td class="align-right" colspan="3">Tax:</td>
							<td>$<?php echo number_format($tax, 2); ?></td>
						</tr>
						<tr>
							<td class="align-right" colspan="3">TOTAL COST:</td>
							<td>$<?php echo number_format($total_cost, 2); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- TODO: Customer Information Here -->
			<div id="customer-info">
				<h3>Customer Information</h3>
				
				<?php
				
				$query = "SELECT customer_id, first_name, last_name, email, street_address, city, state_id, country_name FROM customers NATURAL JOIN countries WHERE customer_id = $_SESSION[customer_id]";
				$result = $conn->query($query);
				$rows = $result->num_rows;
				
				if ($rows !== 1) {
					echo "<p class='red bold'>A serious error has occurred</p>";
					return;
				}
				
				$result->data_seek(0);
				$row = $result->fetch_array(MYSQLI_ASSOC);
				
				$fname = $row['first_name'];
				$lname = $row['last_name'];
				$email = $row['email'];
				$address = $row['street_address'];
				$city = $row['city'];
				$state = ($row['state_id']) ? $row['state_id'] : '';
				$country = $row['country_name'];
					
				echo "<p><span class='bold'>Address:</span><br>$fname $lname<br>$address<br>$city" . (($state) ? ", $state" : '') . "<br>$country<br><br><span class='bold'>Email:</span><br>$email</p>";
				
				?>
				
				<form class="form-horizontal" id="change-address-form" action="myreservations.php" method="post">
				<div class="form-group"> 
					<div class="col-sm-8">
						<button type="submit" class="btn btn-default" name="change-address" id="change-address">Update Customer Info</button>
					</div>
				</div>
				</form>
			
			</div>
			
			<!-- This form isn't actually necessary. It's too dangerous to store credit card info in a database (even salted),
			     and there isn't a way to "decrypt" it even if we did (e.g., for a refund). Also, this form is "validated" using
				 JavaScript instead of PHP. This form only exists to make the customer actually feel like s/he's paying for something. -->
			<h3>Payment Information</h3>
			<p>Please fill out your credit card information below and press the button below to confirm your reservation.</p>
			
			<p id="error-message" class="bold red"></p>
			
			<form class="form-horizontal form-render" id="payment-form" action="confirmation.php" method="post">
				<div class="form-group">
					<label class="control-label col-sm-4" for="credit-card-type">Credit Card Type:</label>
					<div class="col-sm-6">
						<select class="form-control" id="credit-card-type" name="credit-card-type">
							<option value="mastercard" selected>MasterCard</option>
							<option value="visa">Visa</option>
							<option value="americanexpress">American Express</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="credit-card">Credit Card Number:</label>
					<div class="col-sm-6">
						<input type="number" class="form-control" id="credit-card" name="credit-card" placeholder="Enter credit card number" required>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="expiration-month">Expiration Date (Month):</label>
					<div class="col-sm-6">
						<select class="form-control" id="expiration-month" name="expiration-month" required>
							<?php
							
							for ($i = 1; $i <= 12; ++$i) {
								echo ($i < 10) ? "<option value='0$i'>0$i</option>" : "<option value='$i'>$i</option>";
							}
							
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="expiration-year">Expiration Date (Year):</label>
					<div class="col-sm-6">
						<select class="form-control" id="expiration-year" name="expiration-year" required>
							<?php
							
							$current_year = date('Y', time());
							
							for ($i = 0; $i < 6; ++$i) {
								echo "<option value='" . ($current_year + $i) . "'>" . ($current_year + $i) . "</option>";
							}
							
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="credit-card-csc">Card Security Code:</label>
					<div class="col-sm-6">
						<input type="number" class="form-control" id="credit-card-csc" name="credit-card-csc" placeholder="Enter 3 digits on back of credit card" required>
					</div>
				</div>
				
				<!-- Hidden form inputs for preserving data after submission -->
				<?php
				
				foreach ($rooms as $room) {
					echo "<input type='hidden' name='rooms[]' value='$room'>";
				}
				
				foreach ($prices as $price) {
					echo "<input type='hidden' name='prices[]' value='$price'>";
				}
				
				?>
				<input type="hidden" name="nights" value="<?php echo $nights; ?>">
				<input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
				<input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
				<div class="form-group"> 
					<div class="col-sm-12 text-center">
						<button type="submit" class="btn btn-default" name="submit" id="submit">Confirm Payment</button>
					</div>
				</div>
			</div>
		
		<?php
		
		} // end else
			
		?>
		
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
	<script src="../js/reserve.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->$close();
  ?>
</html>