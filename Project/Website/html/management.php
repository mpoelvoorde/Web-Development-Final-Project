<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'management.php';
	
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
    <title>Vacation Inn - Management</title>

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
	$prices = array();
	
	// To 'refill' the form when submitted with errors
	$fname = '';
	$lname = '';
	$address = '';
	$email = '';
	$city = '';

	if (isset($_POST['room-type-change'])) {
	  
		$room_type = $_POST['room-type-select'];

		// Prevent HTML and SQL injection:
		$description = $conn->real_escape_string(htmlspecialchars($_POST['room-type-description']));

		// Description must exist
		if (!$description) {
		  $output .= "New description must not be empty.<br>";
		}

		// Perform update
		else {
		  $query = "UPDATE room_types SET description = '$description' WHERE room_type = '$room_type'";
		  $conn->query($query);
		  $output .= "<span class='green'>Description updated successfully!</span><br>";
		}
	}

	elseif (isset($_POST['room-price-change'])) {
	  
		$prices = $_POST['prices'];
		$query = "UPDATE rooms SET base_price = ? WHERE room_number = ?";
		$query2 = "SELECT room_number, base_price FROM rooms ORDER BY room_number";

		$result = $conn->query($query2);
	  
	  
		if (!$result) {
			die ("Database access failed: " . $conn->error);
		}

		$rows = $result->num_rows;
		$updated_row_count = 0;

		// Update prices if they exist in form and are different than stored price
		for ($i = 0; $i < $rows; ++$i) {
			$result->data_seek($i);
			$row = $result->fetch_array(MYSQLI_NUM);
			
			if ($prices[$i] && ($prices[$i] !== $row[1])) {
				$stmt = $conn->prepare($query);
				$stmt->bind_param('ii', $prices[$i], $row[0]);
				
				if ($stmt->execute()) {
					$output .= "The price of room $row[0] has been successfully updated to $$prices[$i]!<br>";
					++$updated_row_count;
				}
				
				else {
					die('Database access failed: ' . $conn->error);
				}
			}
		}
		
		if ($updated_row_count === 0) {
			$output = "<span class='red'>No prices were changed!</span>";
		}  
	}
	
	// This section processes the form once submitted:
	elseif (isset($_POST['add-employee'])) {
		
		// First, check all fields are set, and 'sanitize' them:
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

		// Check for email uniqueness in BOTH customers and employees (the same appearing in either table is forbidden):
		else {
			
			$query = "SELECT * FROM employees WHERE email = '$email'";		
			$result = $conn->query($query);
					
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			$query = "SELECT * FROM customers WHERE email = '$email'";
			$result = $conn->query($query);
					
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows += $result->num_rows;
			
			if ($rows > 0) {
				$output .= "The email address you entered is already registered to another user. Please enter a different email address.<br>";
			}
		}		
		
		
		// Password must have at least 5 characters
		if (!$_POST['password'] || strlen($_POST['password']) < 5) {
			$output .= 'Please enter a password that has at least 5 characters.<br>';
		}
		
		// If $output is still empty, the form was submitted correctly
		if (strlen($output) === 0) {
			
			// INSERT INTO DATABASE:
			$query = "INSERT INTO employees(first_name, last_name, email, password, street_address, city, state_id, country_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $conn->prepare($query);
			$stmt->bind_param('ssssssss', $fname, $lname, $email, $password, $address, $city, ($state === '') ? null : $state, $country);
			
			if ($stmt->execute()) {
				$output = "<span class='green'>The employee $fname $lname has been registered.</span>";			
			}
			
			else {
				die($stmt->error);
			}
		}
	}
	
	elseif (isset($_POST['employee-delete'])) {
		
		$query = "DELETE FROM employees WHERE employee_id = ?";
		
		if (count($_POST['delete']) === 0) {
			$output .= "<span class='red'>No employees selected for deletion!</span></br>";
		}
		
		foreach ($_POST['delete'] as $employee) {
			$stmt = $conn->prepare($query);
			$stmt->bind_param('i', $employee);
			
			if ($stmt->execute()) {
				$output .= "Employee $employee successfully deleted!<br>";
			}
			
			else {
				die ("Failed to delete employee $employee: " . $conn->error);
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
			Site Management
		</div>
	</div>
	
	<!-- NAVBAR: Content adapted from W3Schools -->
	<nav class="navbar navbar-default employee-navbar">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Management Navigation</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li class="active"><a href="management.php">Management</a></li>
					<li><a href="updateemployee.php">Update Account</a></li>
					<li><a href="reports.php">Reports</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">			
					<!-- We don't need PHP to check for login here since employees MUST be logged in at all times -->
					<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	
	<!-- BODY -->
	<div class="main">
	
	<!-- Form to update room types -->
	<h1 class="bold text-center">Room Types</h1>
	<p class="text-center">Use this form to update the descriptions of every room type in the database.</p>
	<p id="room-type-error-message" class="red bold">
	<?php
	if (isset($_POST['room-type-change'])) {
		echo $output; 
		unset($_POST['room-type-change']);
	}	
	?>
	</p>
	
	<form class="form-horizontal form-render" action="management.php" method="post" id="room-type-select-form">
		<div class="form-group">
			<label id="state-label" class="control-label col-sm-2" for="room-type-select">Room Type:</label>
			<div class="col-sm-10">
				<select class="form-control" id="room-type-select" name="room-type-select">
				<?php
				
				$query = "SELECT room_type, room_type_description FROM room_types";
				$result = $conn->query($query);
				
				if (!$result) {
					die ("Database access failed: " . $conn->error);
				}
				
				$rows = $result->num_rows;
				
				for ($i = 0; $i < $rows; ++$i) {
					$result->data_seek($i);
					$row = $result->fetch_array(MYSQLI_NUM);
					echo "<option value='$row[0]'" . (($i === 0) ? ' selected' : '') . ">$row[1]</option>";
				}
				
				?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="room-type-description" class="control-label col-sm-2">Comment:</label>
			<div class="col-sm-10">
				<textarea class="form-control" rows="5" name="room-type-description" id="room-type-description">

				</textarea>
			</div>
		</div>
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="room-type-change" id="room-type-change">Change Description</button>
			</div>
		</div>
	</form>
	
	<!-- This form changes the prices of rooms in the database -->
	<h1 class="bold text-center">Update Prices</h1>
	<p class="text-center">Use this form to change the individual prices in each room. Prices must be in whole dollar amounts (no decimals allowed).
		If a price is left empty, its value won't be changed in the database.</p>
	<p id="room-price-error-message" class="green bold">
	<?php
	if (isset($_POST['room-price-change'])) {
		echo $output;
		unset($_POST['room-price-change']);
	}	
	?>
	</p>
	
	<form action="management.php" method="post" id="room-price-form">
		<table class="table table-hover table-bordered table-render" id="room-price-table">
			<thead>
				<tr>
					<th>Room</th>
					<th>Type</th>
					<th>Current Price</th>
					<th>New Price</th>
				</tr>
			</thead>
			<tbody>
			<?php
			
			$query = "SELECT room_number, room_type_description, base_price FROM rooms NATURAL JOIN room_types ORDER BY room_number";
			$result = $conn->query($query);
			
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
				
			$rows = $result->num_rows;
			
			for ($i = 0; $i < $rows; ++$i) {
				$result->data_seek($i);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				echo "<tr>";
				
					for ($j = 0; $j < 3; ++$j) {
						echo "<td>$row[$j]</td>";
					}
				
				// Echo input type=number for the new Price
				echo "<td><input type='number' name='prices[]' value='$row[2]'></td>";
				
				echo "</tr>";
			}
			
			?>
			</tbody>
		</table>
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="room-price-change" id="room-price-change">Update Prices</button>
			</div>
		</div>
	</form>
	
	<!-- Now a form to hire new employees -->
	<h1 class="bold text-center">Register New Managers</h1>
	<p class="text-center">This form can be used to register new managers, who will have access to the entire management website.
	Please inform them of their password (in person, of course) so that they can access this website.
	<span class="bold">Please do NOT use this form to update your own personal information. That is done <a href="updateemployee.php">here</a>.</span></p>
	
	<p class="bold red" id="add-employee-error-message">
	<?php
	
	if (isset($_POST['add-employee'])) {
		echo $output;
		unset($_POST['add-employee']);
	}
	?>
	</p>
	
	<!-- Form adapted from W3Schools -->
	<?php echo <<<_END
	<form class="form-horizontal form-render" action="management.php" method="post" id="add-employee-form">
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
				<button type="submit" class="btn btn-default" name="add-employee" id="add-employee">Add Manager</button>
			</div>
		</div>
	</form>
_END;

?>

	<!-- This form can be used to delete employees -->
	<h1 class="text-center bold">Delete Employees</h1>
	<p class="text-center">This form details every employee registered to use the management portion of this website. Should a management employee be terminated, retire,
	or otherwise leave the employment of Vacation Inn, use this form to instantly terminate their access to this website. You cannot use this form to delete yourself.</p>
	<p class="bold green" id="delete-employee-error-message">
	<?php
	if (isset($_POST['employee-delete'])) {
		echo $output;
		unset($_POST['employee-delete']);
	}
	?>
	<form id="dismiss-employees" class="table-render" method="post" action="management.php">
		<table class="table table-hover table-bordered" id="reservation-table">
			<thead>
				<tr>
					<th class="vanish">Employee No.</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th class="vanish">Email</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			
			<?php
			
			$query = "SELECT employee_id, first_name, last_name, email FROM employees";
			$result = $conn->query($query);
			
			if (!$result) {
				die("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			for ($i = 0; $i < $rows; ++$i) {
				
				echo "<tr>";
				
				$result->data_seek($i);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				for ($j = 0; $j < 4; ++$j) {
					
					// Vanish class doesn't render certain (less important) fields for small screens
					echo ($j === 0 || $j === 3) ? "<td class='vanish'>$row[$j]</td>" : "<td>$row[$j]</td>";
				}
				
				// Echo delete checkbox, unless the employee is him/herself
				echo ($row[0] !== $_SESSION['customer_id']) ? "<td><input type='checkbox' name='delete[]' value='$row[0]'></td></tr>" : "<td></td></tr>";
			}
			
			?>
				
			</tbody>
		</table>
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="employee-delete" id="employee-delete">Delete Selected Employees</button>
			</div>
		</div>
		
	</form>
	
	
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	<script src="../js/management.js"></script>
	
  </body>
  

</html>