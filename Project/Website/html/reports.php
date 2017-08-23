<?php
	session_start();
	//require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'reports.php';
	
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
    <title>Vacation Inn - Reports</title>

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
	
	<!-- Google Charts Script -->
	<script src="https://www.gstatic.com/charts/loader.js"></script>
	
  </head>
  <body>
  
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
					<li><a href="management.php">Management</a></li>
					<li><a href="updateemployee.php">Update Account</a></li>
					<li class="active"><a href="reports.php">Reports</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">			
					<!-- We don't need PHP to check for login here since employees MUST be logged in at all times -->
					<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="main">
	<h1 class="text-center bold">Reports</h1>
	<p class="text-center">Use this tool to analyze revenue and occupancy data, for the whole hotel or room-by-room. This data can be used to weekly evaluate sales trends,
	and to see whether room discounts are needed to fill up next week's room bookings.</p>
	<div class="form-horizontal form-render" id="sorting-form">
		<div class="form-group">
			<label class="control-label col-sm-2" for="type">Report Type:</label>
			<div class="col-sm-10">
				<select class="form-control" id="type" name="type">
					<option value="o" selected>Occupancy Rate</option>
					<option value="r">Revenue</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="report-type">Group Results By:</label>
			<div class="col-sm-10">
				<select class="form-control" id="group" name="group">
					<option value="t" selected>Entire Hotel</option>
					<option value="r">Individual Rooms</option>
				</select>
			</div>
		</div>
	</div>
	<h3 class="bold text-center" id="chart-title"></h3>
	<div id="chart"></div>
	
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal JS Script -->
	<script src="../js/reports.js"></script>
  </body>
  
  <?php
	//$result->close();
	//$conn->$close();
  ?>
</html>