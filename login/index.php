<?php

$root = $_SERVER["DOCUMENT_ROOT"];
require_once($root . "/db.php");

if (!empty($_SESSION['user'])) {
	// already logged in, redirect the user
	header("Location: /");
	die("Redirecting");
}

if (isset($_POST)) {
	$user = '';
	$pass = '';

	if (empty($_POST['username'])) {
		// echo "Please enter a username";
	}
	else {
		$user = mysqli_real_escape_string($connect, $_POST['username']);
	}

	if (empty($_POST['password'])) {
		// echo "Please enter a password";
	}
	else {
		$pass = mysqli_real_escape_string($connect, $_POST['password']);
	}
	

	$select = "
		SELECT
			id,
			first_name,
			last_name,
			username,
			password,
			points
		FROM users
		WHERE
			username = '{$user}' AND
			password = '{$pass}'
	";

	$query = mysqli_query($connect, $select);
	$rows  = mysqli_num_rows($query);

	if ($rows == 1) {
		$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

		// Remove password
		unset($row['password']);

		$_SESSION['user'] = $row;

		header('Location: /');
		die("Redirecting");
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Quiz</title>
	<link rel="stylesheet" type="text/css" href="/index.css">
	<style type="text/css">
		input
{
	border: 0;
	outline: 0;
	background: transparent;
	border-bottom: 2px #d3d3d3 solid;
}
	</style>
</head>
<body>
<span>H</span><span>A</span><span>C</span><span>K</span>
<form method="post" action="/login/index.php">
	<input type="username" name="username" placeholder="Username" autocomplete="off"> <br> <br>
	<input type="password" name="password" placeholder="Password" autocomplete="off"> <br><br>

	<button type="submit" id="loginbtn" name="submit">Login </button>
</form>
<a href="register.php" id="register"> Register </a>
</body>
</html>
