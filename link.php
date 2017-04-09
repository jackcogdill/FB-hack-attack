<?php
require_once('db.php');

$user = $_SESSION['user']['username'];
$select = "
	SELECT *
	FROM ongoing
	WHERE user1 = '{$user}'
";

$query = mysqli_query($connect, $select);
$rows  = mysqli_num_rows($query);

if ($rows == 1) {
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	if (isset($_SESSION['user']['waiting'])) {
		unset($_SESSION['user']['waiting']);
	}
	$_SESSION['user']['hash_id'] = $row['id'];
	echo 'index.php';
}

?>
