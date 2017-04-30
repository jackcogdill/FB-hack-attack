<?php

// Connect to database and start session
require_once("../secure.php");

// Reset any matches
// =====================
// Ongoing
$user = $_SESSION['user']['username'];
$og_stmt = $connect->prepare('
	DELETE FROM ongoing
	WHERE (
		user1 = ? OR
		user2 = ?
	)
');
if ($og_stmt) {
	$og_stmt->bind_param(
		"ss",
		$user,
		$user
	);
	$og_stmt->execute();
	$og_stmt->close();
}

// Waiting
$wait_stmt = $connect->prepare('
	DELETE FROM waiting
	WHERE username = ?
');
if ($wait_stmt) {
	$wait_stmt->bind_param(
		"s",
		$user
	);
	$wait_stmt->execute();
	$wait_stmt->close();
}
// End reset matches
// =====================

// Remove user data from session
unset($_SESSION['user']);

// Redirect to login page
header("Location: ../login");
die("Redirecting");

?>
