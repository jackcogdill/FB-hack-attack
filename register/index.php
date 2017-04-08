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

require_once($root . "/head_top.php");
require_once($root . "/head_bottom.php");

?>


<form class="login" action="/register/index.php" method="POST">
	<input class="login" type="text" name="first_name" placeholder="First Name"> <br> <br>
	<input class="login" type="text" name="last_name" placeholder="Last Name"> <br> <br>
	<input class="login" type="text" name="username" placeholder="Username"> <br><br>
	<input class="login" type="email" name="email" placeholder="Email"> <br><br>
	<input class="login" type="password" name="password" placeholder="Password"> <br> <br>
	<button class="login" type="submit" name="submit">Register</button>
</form>


<?php
require_once($root . "/footer.php");
?>
