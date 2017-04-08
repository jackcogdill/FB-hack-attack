<?php

$root = $_SERVER["DOCUMENT_ROOT"];
require_once($root . "/db.php");

if (isset($_POST['submit'])) {
	$first_name = mysqli_real_escape_string($connect, ($_POST['first_name']) );
	$last_name  = mysqli_real_escape_string($connect, ($_POST['last_name'])  );
	$username   = mysqli_real_escape_string($connect, ($_POST['username'])   );
	$email      = mysqli_real_escape_string($connect, ($_POST['email'])      );
	$password   = mysqli_real_escape_string($connect, ($_POST['password'])   );
	$points     = mysqli_real_escape_string($connect, 0                      );

	$stmt = $connect->prepare("INSERT INTO users (first_name, last_name, username, email, password, points) VALUES(?, ?, ?, ?, ?, ?)");

	if(!$stmt) {}
	else {
		$stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $password, $points);
		$stmt->execute();
		$rows =  $stmt->affected_rows;
		$stmt->close();

		// Redirect the user
		header("Location: /login?registered");
		die("Redirecting");
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		form
		{
			position: absolute;
			top: 230px;
			left: 500px;
		}
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
<form action="/register/index.php" method="POST">
	<input type="text" name="first_name" placeholder="First Name"> <br> <br>
	<input type="text" name="last_name" placeholder="Last Name"> <br> <br>
	<input type="text" name="username" placeholder="Username"> <br><br>
	<input type="email" name="email" placeholder="Email"> <br><br>
	<input type="password" name="password" placeholder="Password"> <br> <br>
	<button type="submit" id="register" name="submit">Register</button>
</form>
</body>
</html>
