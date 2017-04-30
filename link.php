<?php
require_once('db.php');

$user = $_SESSION['user']['username'];
$stmt = $connect->prepare('
	SELECT *
	FROM ongoing
	WHERE
		user1 = ? OR user2 = ?
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

		if (isset($_SESSION['user']['waiting'])) {
			unset($_SESSION['user']['waiting']);
		}
		$_SESSION['user']['hash_id'] = $row['id'];
		echo 'index.php';
	}

	$stmt->close();
}

?>
