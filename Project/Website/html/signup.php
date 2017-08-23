
<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'signup.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
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
	
	// To 'refill' the form when submitted with errors
	$fname = '';
	$lname = '';
	$address = '';
	$email = '';
	$city = '';
	
	// This section processes the form once submitted:
	if (isset($_POST['submit'])) {
		
		// First, check all fields are set, and 'sanitize' them with both htmlspecialchars and real_escape_string.
		// This ensures that data both stored in and retrieved from the database can't be used to "inject" SQL and/or HTML into a page or database on this website.
		// We don't need to use htmlspecialchars on country or state since those can only be entered in via a "value" attribute on predefined <select> options,
		// that don't have any special characters in them.
		$fname = $conn->real_escape_string(htmlspecialchars($_POST['fname']));
		$lname = $conn->real_escape_string(htmlspecialchars($_POST['lname']));
		$address = $conn->real_escape_string(htmlspecialchars($_POST['address']));
		$city = $conn->real_escape_string(htmlspecialchars($_POST['city']));
		$country = $conn->real_escape_string($_POST['country']);
		$state = ($_POST['state']) ? $conn->real_escape_string($_POST['state']) : ''; // State may be empty, so we should check for this to be safe
		$email = $conn->real_escape_string(htmlspecialchars($_POST['email']));
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
			
			if ($rows > 0) {
				$output .= "The email address you entered is already registered to another user. Please enter a different email address.<br>";
			}
		}		
		
		
		// Password must have at least 5 characters
		if (!$_POST['password'] || strlen($_POST['password']) < 5) {
			$output .= 'Please enter a password that has at least 5 characters.<br>';
		}
		
		unset($_POST['submit']); // This doesn't actually work since browsers resubmit form data on refresh
		
		// If $output is still empty, the form was submitted correctly
		if (strlen($output) == 0) {
			
			// INSERT INTO DATABASE:
			$query = "INSERT INTO customers(first_name, last_name, email, password, street_address, city, state_id, country_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $conn->prepare($query);
			$stmt->bind_param('ssssssss', $fname, $lname, $email, $password, $address, $city, ($state === '') ? null : $state, $country);
			
			if ($stmt->execute()) {
				$output = "Welcome $fname $lname, your account has been created and you are logged in.<br>";
				$_SESSION['login'] = true;
				
				// TODO: Identify customer number for future reference when booking/searching for past bookings on website:
				$query = "SELECT customer_id FROM customers WHERE email = '$email'";
				$result = $conn->query($query);
					
					if (!$result) {
						die ("Database access failed: " . $conn->error);
					}
					
					$rows = $result->num_rows;
					
					// This should never happen, and would indicate that duplicate emails somehow exist in the database
					if ($rows != 1) {
						$output .= "Unexpected database error! Try using our website later after it's fixed!<br>";
					}
					
					// This should always happen, since emails should be unique
					else {
						$result->data_seek(0);
						$row = $result->fetch_array(MYSQLI_NUM);
						$_SESSION['customer_id'] = $row[0]; // Store customer_id in session
					}
				
			} else {
				die($stmt->error);
			}
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
					<li><a href="myreservations.php">Account and Reservations</a></li> 
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
	
	// If form previously not submitted correctly
	echo ($_SESSION['login']) ? "<span class= 'bold'>$output</span>" : "<span class='bold red'>$output</span>";
	
	// If not logged in
	if (!$_SESSION['login']) {
		echo <<<_END
			<h1 class="bold text-center">Create An Account</h1>
			<p class="text-center">All fields are required unless grayed out</p>
			
			<!-- Form adapted from W3Schools -->
			<form class="form-horizontal form-render" action="signup.php" method="post">
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
						
						// Select "Canada" by default
						if ($row[0] == 'CAN') {
							echo "<option value='CAN' selected>Canada</option>";
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
						<input type="password" class="form-control" id="password" name="password" placeholder="Enter password (at least 5 characters)">
					</div>
				</div>
				<div class="form-group"> 
					<div class="col-sm-12 text-center">
						<button type="submit" class="btn btn-default" name="submit" id="submit">Submit</button>
					</div>
				</div>
			</form>
_END;
	}

	else {
		echo ($output) ? '' : '<h1 class="bold text-center">You are already logged in!</h1>';
	}
	
	?>
	
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	<script src="../js/signup.js"></script>
  </body>
  
  <?php
	if (isset($result)) {
		$result->close();
	}
	$conn->$close();
  ?>
</html>
