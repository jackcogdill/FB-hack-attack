<?php

require_once("db.php");

// Check if user is logged in
if (empty($_SESSION['user'])) {
	// If not, redirect to login page
	header("Location: login");
	
	// Must die
	die("Redirecting");
}

?>
