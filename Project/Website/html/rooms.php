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
	if (!$_SESSION['login']) {
		header("Location: login.php");
	}
	
	// This session variable is used to prevent duplicate room insertions on a different page.
	// It needs to be unset for the user to make more room reservations.
	if (isset($_SESSION['refresh'])) {
		unset($_SESSION['refresh']);
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
    <title>Vacation Inn - Rooms</title>

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
	
	$date = date('Y-m-d'); // Today's date
	
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
					<li class="active"><a href="rooms.php">Rooms</a></li>
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
		<h1 class="bold text-center">Browse Our Rooms</h1>
		
		<p id="error-message" class="bold red"></p>
		
		<!-- According to stack overflow, replacing "form" with "div" will still render the form how I want it,
		     since this "form" is not intended to be submitted. Each input calls AJAX.
			 Note that the user will have to resumbit this if s/he wishes to change the dates of their stay,
			 since the cart will be automatically emptied if the date changes.-->
		<div class="form-horizontal" id="date-form" class="form-render">
			<div class="form-group">
				<label class="control-label col-sm-4" for="date">Reservation Start Date:</label>
				<div class="col-sm-6">
					<input type="date" class="form-control" id="date" name="date" placeholder="Enter date" value="<?php echo $date; ?>" required>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-4" for="nights">Number of Nights:</label>
				<div class="col-sm-6">
					<select class="form-control" id="nights" name="nights">
						<?php
						
							for ($i = 1; $i <= 7; ++$i) {
								echo "<option value='$i'>$i</option>";
							}
						
						?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-4" for="beds">Number of Beds:</label>
				<div class="col-sm-6">
					<select class="form-control" id="beds" name="beds">
						<option value="0" selected>Any</option>
						<option value="1">1</option>
						<option value="2">2</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-4" for="order">Order Results By:</label>
				<div class="col-sm-6">
					<select class="form-control" id="order" name="order">
						<option value="0" selected>Room Number</option>
						<option value="1">Price (lowest first)</option>
						<option value="2">Price (highest first)</option>
					</select>
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-4 col-sm-8">
					<button type="button" class="btn btn-default" name="submit-date" id="submit-date">Submit</button>
				</div>
			</div>
		</div>
		
		<div id="room-content">
		
		</div>
		
		<div id="reselect-rooms">
			<button type='button' class='btn btn-primary' id='new-date' name='new-date'>Change Dates</button></p>
		</div>
		
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
	<script src="../js/rooms.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->$close();
  ?>
</html>