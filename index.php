<?php

$root = $_SERVER["DOCUMENT_ROOT"];
require_once($root . "/secure.php");

$name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset="utf-8">
		<title>Test</title>
		<link rel="stylesheet" href="assets/stylesheets/main.css">
	</head>
	<body>
		<div>Welcome, <?php echo $name; ?>.</div>
		<a href="/logout">Logout</a>
	</body>
</html>
