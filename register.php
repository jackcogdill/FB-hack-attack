<?php

require_once("db.php");

if (isset($_POST['submit'])) {
	$first_name = mysqli_real_escape_string($connect, ($_POST['first_name']));
	$last_name = mysqli_real_escape_string($connect, ($_POST['last_name']));
	$username = mysqli_real_escape_string($connect, ($_POST['username']));
	$email = mysqli_real_escape_string($connect, ($_POST['email']));
	$password = mysqli_real_escape_string($connect, ($_POST['password']));
	
	$stmt = $connect->prepare("INSERT INTO users (first_name, last_name, username, email, password) VALUES(?, ?, ?, ?, ?)");

	if(!$stmt) {}
	else {
		$stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $password);
		$stmt->execute();
		$rows =  $stmt->affected_rows;
		$stmt->close();
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<form action="register.php" method="POST">
	<input type="text" name="first_name" placeholder="First Name"> <br> <br>
	<input type="text" name="last_name" placeholder="Last Name"> <br> <br>
	<input type="text" name="username" placeholder="Username"> <br><br>
	<input type="email" name="email" placeholder="Email"> <br><br>
	<input type="password" name="password" placeholder="Password"> <br> <br>
	<button type="submit" id="register" name="submit">Register</button>
</form>
</body>
</html>
