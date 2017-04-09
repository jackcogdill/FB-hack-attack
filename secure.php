<?php

if (!isset($up)) {
	$up = '';
}

require_once($up . "db.php");

// Check if user is logged in
if (empty($_SESSION['user'])) {
	// If not, redirect to login page
	header("Location: ".$up."login");
	
	// Must die
	die("Redirecting");
}

?>
