<?php

// For local testing
// ==================================================================
// define("DB_SERVER", "localhost");
// define("DB_USER", "quizer");
// define("DB_PASSWORD", "quizme");
// define("DB_NAME", "quiz");
// $connect = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);


// For site on heroku
// ==================================================================
$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);

$connect = new mysqli($server, $username, $password, $db);
mysqli_set_charset($connect, "utf8");

if (mysqli_connect_errno()) {
	// die("Database connection failed: " .
	// 	mysqli_connect_error() .
	// 	" (" . mysqli_connect_errno(). ")"
	// 	);
	die("Failed to connect to database");
}

session_start();

?>
