<?php

$up = "../";
require_once("../db.php");

$bad_notice = '';

if (isset($_GET['utaken'])) {
	$bad_notice = 'Username has already been taken';
}
else if (isset($_GET['etaken'])) {
	$bad_notice = 'Email has already been taken';
}
else if (isset($_GET['blank'])) {
	$bad_notice = 'Cannot leave fields blank';
}

if (isset($_POST['submit'])) {
	$first_name = mysqli_real_escape_string($connect, ($_POST['first_name']) );
	$last_name  = mysqli_real_escape_string($connect, ($_POST['last_name'])  );
	$username   = mysqli_real_escape_string($connect, ($_POST['username'])   );
	$email      = mysqli_real_escape_string($connect, ($_POST['email'])      );
	$password   = mysqli_real_escape_string($connect, ($_POST['password'])   );
	$points     = mysqli_real_escape_string($connect, 0                      );

	if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
		header('Location: ../register/index.php?blank');
		die("Redirecting");
	}

	// Make sure username is unique
	$select = "
		SELECT username
		FROM users
		WHERE username = '{$username}'
	";

	$query = mysqli_query($connect, $select);
	$rows  = mysqli_num_rows($query);

	if ($rows != 0) {
		header('Location: ../register/index.php?utaken');
		die("Redirecting");
	}

	// Make sure email is unique
	$select = "
		SELECT email
		FROM users
		WHERE email = '{$email}'
	";

	$query = mysqli_query($connect, $select);
	$rows  = mysqli_num_rows($query);

	if ($rows != 0) {
		header('Location: ../register/index.php?etaken');
		die("Redirecting");
	}


	$stmt = $connect->prepare("INSERT INTO users (first_name, last_name, username, email, password, points) VALUES(?, ?, ?, ?, ?, ?)");

	if ($stmt) {
		$stmt->bind_param("sssssi", $first_name, $last_name, $username, $email, $password, $points);
		$stmt->execute();
		$rows =  $stmt->affected_rows;
		$stmt->close();

		// Redirect the user
		header("Location: ../login?registered");
		die("Redirecting");
	}
}

require_once("../head_top.php");
require_once("../head_bottom.php");
?>

<p class="logo"> Hack Attack </p>
<form class="login" action="../register/index.php" method="POST">
<?php
if (!empty($bad_notice)) {
?>
<div class="badnotice">
<?php echo $bad_notice; ?>
</div>
<?php
}
?>
	<input class="login" type="text" name="first_name" placeholder="First Name"> <br> <br>
	<input class="login" type="text" name="last_name" placeholder="Last Name"> <br> <br>
	<input class="login" type="text" name="username" placeholder="Username"> <br><br>
	<input class="login" type="email" name="email" placeholder="Email"> <br><br>
	<input class="login" type="password" name="password" placeholder="Password"> <br> <br>
	<button class="login" type="submit" name="submit">Register</button>
</form>


<?php
require_once("../footer.php");
?>
