<?php

$up = "../";
require_once("../db.php");

if (!empty($_SESSION['user'])) {
	// already logged in, redirect the user
	header("Location: ../");
	die("Redirecting");
}

$notice = '';

if (isset($_GET['registered'])) {
	$notice = 'Successfully registered! Now you can login below.';
}
elseif (isset($_GET['ip'])) {
	$notice = 'Incorrect password.';
}

if (isset($_POST)) {
	$user   = '';
	$pass   = '';

	if (!empty($_POST['username'])) {
		$user = $_POST['username'];
	}

	if (!empty($_POST['password'])) {
		$pass = $_POST['password'];
	}

	$stmt = $connect->prepare('
		SELECT
			id,
			first_name,
			last_name,
			username,
			password,
			salt,
			points
		FROM users
		WHERE
			(username = ? OR email = ?)
	');
	if ($stmt) {
		$stmt->bind_param(
			"ss",
			$user,
			$user
		);
		$stmt->execute();

		$result = $stmt->get_result();
		if ($result->num_rows === 1) {
			$row = $result->fetch_assoc(); // Get row

			// Compare hashes to check if correct password
			for ($round = 0; $round < 65536; $round++) {
				$pass = hash('sha512', $pass . $row['salt']);
			}

			if ($pass === $row['password']) {
				// Remove sensitive values even though session is stored server side
				unset($row['salt']);
				unset($row['password']);

				$_SESSION['user'] = $row;

				header('Location: ../');
				die("Redirecting");
			}
			else {
				header('Location: ../login/index.php?ip');
				die("Redirecting");
			}
		}

		$stmt->close();
	}
}

require_once("../head_top.php");
require_once("../head_bottom.php");
?>
<div id="wrap">
<a class="logo" href="../"> Hack Attack </a>
<form class="login" method="post" action="../login/index.php">
<?php
if (!empty($notice)) {
?>
<div class="notice">
<?php echo $notice; ?>
</div>
<?php
}
?>
	<input class="login" type="username" name="username" placeholder="Username" autocomplete="off"> <br> <br>
	<input class="login" type="password" name="password" placeholder="Password" autocomplete="off"> <br><br>

	<button type="submit" class="login" name="submit">Login</button>
</form>
<div id="login-after">
	Not signed up? <a href="../register" id="register">Register</a>
</div>

<?php
require_once("../footer.php");
?>
