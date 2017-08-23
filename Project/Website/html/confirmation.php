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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <!-- This website uses the Bootstrap default webpage layout as a starting point -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Vacation Inn - Reservation Confirmation</title>

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
	$prices = $_POST['prices'];
	$nights = $_POST['nights'];
	$start_date = $conn->real_escape_string($_POST['start_date']);
	$end_date = $conn->real_escape_string($_POST['end_date']);
	$customer_id = $conn->real_escape_string($_SESSION['customer_id']);
	$today = date('Y-m-d', time());
	$output = '';
	$error = false;
	
	// Prevent duplicate insertions upon refreshing page:
	if (!isset($_SESSION['refresh'])) {
		$_SESSION['refresh'] = false;
	}
	
	if (!$_SESSION['refresh']) {
		// Reserve each room, one at a time:
		$i = 0;
		foreach ($rooms as $room) {
			$query = "INSERT INTO reservations(customer_id, room_number, start_date, end_date, reservation_date, price) VALUES(?, ?, ?, ?, ?, ?)";
			
			$stmt = $conn->prepare($query);
			$stmt->bind_param('iisssi', $customer_id, $room, $start_date, $end_date, $today, $prices[$i++]);
			
			if ($stmt->execute()) {
				$output .= "Room $room reserved successfully!<br>";	
			}
			
			else {
				$output .= "There was an error trying to reserve Room $room. Please try again later.<br>";
				$error = true;
				die($stmt->error);
			}
		}
		
		$_SESSION['refresh'] = true;
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
	<?php
	
	if ($error) {
		echo "<h1 class='bold text-center'>Reservation Failed</h1>";
		echo "<p>$output</p>";
	}
		
	else {
		
	?>
	
		<h1 class='bold text-center'>Reservation Successful</h1>
		<p class='bold text-center'>You have successfully reserved your rooms.</p>
		<p class='text-center'><a href='myreservations.php'><button type='button' class='btn btn-default' id='to-reservations' name='to-reservations'>View My Reservations</button></a></p>
		
	<?php
	
	} // end else
		
	?>
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
  </body>
  
  <?php
	$result->close();
	$conn->$close();
  ?>
</html>