<?php

// Connect to database and start session
require_once("../secure.php");

// Remove user data from session
unset($_SESSION['user']);

// Redirect to login page
header("Location: /login");
die("Redirecting");

?>
