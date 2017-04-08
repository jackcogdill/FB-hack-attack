<?php

// Connect to database and start session
$root = $_SERVER["DOCUMENT_ROOT"];
require_once($root . "/secure.php");

// Remove user data from session
unset($_SESSION['user']);

// Redirect to login page
header("Location: /login");
die("Redirecting");

?>
