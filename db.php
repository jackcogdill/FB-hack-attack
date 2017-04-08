<?php

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);

$conn = new mysqli($server, $username, $password, $db);
mysqli_set_charset($conn, "utf8");

if (mysqli_connect_errno()) {
	// die("Database connection failed: " .
	// 	mysqli_connect_error() .
	// 	" (" . mysqli_connect_errno(). ")"
	// 	);
	die("Failed to connect to database");
}

?>
