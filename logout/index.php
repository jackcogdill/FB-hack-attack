<?php

// Connect to database and start session
require_once("../secure.php");

// Reset any matches
// =====================
// Ongoing
$user = $_SESSION['user']['username'];
$select = "
	DELETE FROM ongoing
	WHERE (
		user1 = '{$user}' OR
		user2 = '{$user}'
	)
";
$query = mysqli_query($connect, $select);

// Waiting
$select = "
	DELETE FROM waiting
	WHERE username = '{$user}'
";
$query = mysqli_query($connect, $select);
// End reset matches
// =====================

// Remove user data from session
unset($_SESSION['user']);

// Redirect to login page
header("Location: ../login");
die("Redirecting");

?>
