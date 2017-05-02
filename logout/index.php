<?php

// Connect to database and start session
require_once("../secure.php");

// Delete from any ongoing games
$user = $_SESSION['user']['username'];
$delo_stmt = $connect->prepare('
	DELETE FROM ongoing
	WHERE (
		user1 = ? OR
		user2 = ?
	)
	LIMIT 1
');
if ($delo_stmt) {
	$delo_stmt->bind_param(
		"ss",
		$user,
		$user
	);
	$delo_stmt->execute();
	$delo_stmt->close();
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
header("Location: /login");
die("Redirecting");

?>
