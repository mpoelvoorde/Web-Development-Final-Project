<?php
	session_start();
	require_once 'databaseinfo.php';
	
	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'index.php';
	
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
    <title>Vacation Inn - Welcome!</title>

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
					<li class="active"><a href="index.php">Home</a></li>
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
	
	
	<!-- BODY -->
	<div class="main">
	<h1 class="bold text-center">Welcome to Vacation Inn!</h1>
	<p class="text-center">Vacation Inn has been Windsor's best destination for individual or family lodging since 2017. Whether you're looking for basic accommodation or a luxurious suite, Vacation Inn offers rooms for everyone, just minutes from Windsor's best attractions!</p> 
	
	<!-- This following section implements Bootstrap Carousel code from W3Schools -->
	<div id="myCarousel" class="carousel slide" data-ride="carousel">
		<!-- Indicators -->
		<ol class="carousel-indicators">
			<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
			<li data-target="#myCarousel" data-slide-to="1"></li>
			<li data-target="#myCarousel" data-slide-to="2"></li>
			<li data-target="#myCarousel" data-slide-to="3"></li>
			<li data-target="#myCarousel" data-slide-to="4"></li>
		</ol>

		<!-- Wrapper for slides -->
		<div class="carousel-inner">
			<div class="item active">
				<img src="../pictures/hotel-waterfront.jpg" alt="Vacation Inn Exterior">
				<div class="carousel-caption">
					<h3>Riverfront Location</h3>
					<p>Vacation Inn is conveniently located just steps from the Detroit River with great waterfront views!</p>
				</div>
			</div>

			<div class="item">
				<img src="../pictures/conference-room.jpg" alt="Private Conference and Dining Room">
				<div class="carousel-caption">
					<h3>Private Conference and Dining Room</h3>
					<p>Vacation Inn is ideal for hosting your conferences or parties. Ask our staff about booking this room today!</p>
				</div>
			</div>

			<div class="item">
				<img src="../pictures/hotel-beach.jpg" alt="Vacation Inn is Right on the Beach!">
				<div class="carousel-caption">
					<h3>Our Pristine Beach</h3>
					<p>All occupants of Vacation Inn gain access to our luxurious private beach!</p>
				</div>
			</div>
			
			<div class="item">
				<img src="../pictures/hotel-restaurant.jpg" alt="Our Full-Service Restaurant">
				<div class="carousel-caption">
					<h3>Hungry?</h3>
					<p>Whether you're staying at Vacation Inn or not, make sure to try our restaurant! Free daily breakfast for guests!</p>
				</div>
			</div>
			
			<div class="item">
				<img src="../pictures/indoor-pool.jpg" alt="Our Indoor Pool">
				<div class="carousel-caption">
					<h3>Indoor Pool</h3>
					<p>If it's too cool outside to visit our lovely beach, relax in our indoor pool instead!</p>
				</div>
			</div>
		</div>

		<!-- Left and right controls -->
		<a class="left carousel-control" href="#myCarousel" data-slide="prev">
			<span class="glyphicon glyphicon-chevron-left"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="right carousel-control" href="#myCarousel" data-slide="next">
			<span class="glyphicon glyphicon-chevron-right"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>

	</div>
	
	<div class="footer">
		<h3 class="bold">Located just 5 minutes away from Caesar's Windsor casino!</h2>
		<!-- Footer panels -->
		<div class="row">
			<div class="col-sm-4">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title">Our Location</h3>
					</div>
					<div class="panel-body">
						401 Sunset Avenue<br>
						Windsor, ON<br>
						N9B 3P4
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title">Vacancy Information</h3>
					</div>
					<div class="panel-body">
					<?php
					
					// Determine how many (1-night) bookings are available tonight
					$today = date('Y-m-d', time());
					$query = "SELECT room_number FROM reservations NATURAL JOIN rooms WHERE start_date <= '$today' AND end_date > '$today'";
				
					$result = $conn->query($query);
					$rows = $result->num_rows;
					
					// $rows stores the number of rooms NOT available. We want the number that ARE.
					$query = "SELECT * FROM rooms";
					$result = $conn->query($query);
					$total_rooms = $result->num_rows;
					
					$available_rooms = $total_rooms - $rows;
					
					switch ($available_rooms) {
						case 0:
							echo "No rooms available tonight!";
							break;
						case 1:
							echo "1 room available tonight!";
							break;
						default:
							echo "$available_rooms rooms available tonight!";
							break;
					}
					
					?>
					
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title">Open 24/7/365!</h3>
					</div>
					<div class="panel-body">
						Vacation Inn is always ready to meet your lodging needs, anytime!
					</div>
				</div>
			</div>
		</div>
		
		<!-- The site I used to make my logo required me to say this line below somewhere on my website -->
		<p>Vacation Inn logo made with <a href="https://logomakr.com">Logomakr.com</a></p>
	</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->$close();
  ?>
</html>