<?php

$up = "../";
require_once("../db.php");

$bad_notice = '';

if (isset($_GET['utaken'])) {
	$bad_notice = 'Username has already been taken.';
}
elseif (isset($_GET['etaken'])) {
	$bad_notice = 'Email has already been taken.';
}
elseif (isset($_GET['blank'])) {
	$bad_notice = 'Cannot leave fields blank.';
}

if (isset($_POST['submit'])) {
	$first_name = $_POST['first_name'];
	$last_name  = $_POST['last_name'];
	$username   = $_POST['username'];
	$email      = $_POST['email'];
	$password   = $_POST['password'];
	$points     = 0;

	if (
		empty($first_name) || empty($last_name) || empty($username) ||
		empty($email)      || empty($password)
	) {
		header('Location: ../register/index.php?blank');
		die("Redirecting");
	}

	// Make sure username is unique
	$usr_stmt = $connect->prepare('
		SELECT username
		FROM users
		WHERE username = ?
	');
	if ($usr_stmt) {
		$usr_stmt->bind_param(
			"s",
			$username
		);
		$usr_stmt->execute();

		$result = $usr_stmt->get_result();
		if ($result->num_rows !== 0) {
			header('Location: ../register/index.php?utaken');
			die("Redirecting");
		}

		$usr_stmt->close();
	}

	// Make sure email is unique
	$eml_stmt = $connect->prepare('
		SELECT email
		FROM users
		WHERE email = ?
	');
	if ($eml_stmt) {
		$eml_stmt->bind_param(
			"s",
			$email
		);
		$eml_stmt->execute();

		$result = $eml_stmt->get_result();
		if ($result->num_rows !== 0) {
			header('Location: ../register/index.php?etaken');
			die("Redirecting");
		}

		$eml_stmt->close();
	}


	// Prevent brute forcing and rainbow tables with a random salt
	$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));

	// Hashing this loses the original password
	// Protect against rainbow tables and brute forcing
	// Rehashing this 2^16 times with salt
	for($round = 0; $round < 65536; $round++) {
		$password = hash('sha512', $password . $salt);
	}

	// Not storing the original password, just the hashed
	// version of it (with the random salt)
	$stmt = $connect->prepare('
		INSERT INTO users
			(first_name, last_name, username, email, password, salt, points)
		VALUES
			(?, ?, ?, ?, ?, ?, ?)
	');

	if ($stmt) {
		$stmt->bind_param(
			"ssssssi",
			$first_name,
			$last_name,
			$username,
			$email,
			$password,
			$salt,
			$points
		);
		$stmt->execute();
		$stmt->close();

		// Redirect the user
		header("Location: ../login?registered");
		die("Redirecting");
	}
}

require_once("../head_top.php");
require_once("../head_bottom.php");
?>

<a class="logo" href="../"> Hack Attack </a>
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
