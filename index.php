<?php

session_start();
require_once("db.php");

if (isset($_POST['submit'])) {
	$email = mysqli_real_escape_string($connect, $_POST['email']);
	$password = mysqli_real_escape_string($connect, $_POST['password']);
	$select = "SELECT * FROM users WHERE email = '{$email}' AND password = '{$password}'";
	$query = mysqli_query($connect, $select);
	$rows = mysqli_num_rows($query);
	echo $rows;
	if ($rows == 1) {
		while($userRow = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$_SESSION['first_name'] = $userRow['first_name'];
			$_SESSION['last_name']  = $userRow['last_name'];
		}
		header('Location: home.php');
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Quiz</title>
	<link rel="stylesheet" type="text/css" href="index.css">
</head>
<body>
<span>H</span><span>A</span><span>C</span><span>K</span>
<form method="post" action="index.php">
	<input type="email" name="email" placeholder="Email" autocomplete="off"> <br> <br>
	<input type="password" name="password" placeholder="Password" autocomplete="off"> <br><br>

	<button type="submit" id="loginbtn" name="submit">Login </button>
</form>
<a href="register.php" id="register"> Register </a>
</body>
</html>
