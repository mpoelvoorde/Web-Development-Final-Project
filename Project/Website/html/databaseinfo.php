<?php

	// For accessing database:
	$db = ''; // REDACTED FOR SECURITY REASONS
	$hn = ''; // REDACTED
	$un = ''; // REDACTED
	$pw = ''; // REDACTED
	
	// For salting passwords:
	$pre_salt = ''; // REDACTED
	$post_salt = ''; // REDACTED
	
	// To establish the connection on each page
	$conn = new mysqli($hn, $un, $pw, $db);
	if ($conn->connect_error) {
		die($conn->connect_error);
	}
	
	// A function that fully sanitizes and hashes a password input
	/*
	function sanitize($password) {
		echo $password;
		$hashed_password = hash('ripemd128', "$pre_salt$password$post_salt");
		return $conn->real_escape_string($hashed_password);
	} */

?>
