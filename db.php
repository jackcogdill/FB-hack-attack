<?php

define("DB_SERVER", "localhost");
define("DB_USER", "quizer");
define("DB_PASSWORD", "quizme");
define("DB_NAME", "quiz");

$connect = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
mysqli_set_charset($connect, "utf8");

if (mysqli_connect_errno()) {
	// die("Database connection failed: " .
	// 	mysqli_connect_error() .
	// 	" (" . mysqli_connect_errno(). ")"
	// 	);
	die("Failed to connect to database");
}

?>
