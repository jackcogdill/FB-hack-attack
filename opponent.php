<?php
require_once('db.php');

if (!isset($_SESSION['user']['hash_id'])) {
	die();
}

$final_data = array(
	'text' => '',
	'left' => '0'
);

$user = $_SESSION['user']['username'];
$id   = $_SESSION['user']['hash_id'];
$stmt = $connect->prepare('
	SELECT *
	FROM ongoing
	WHERE id = ?
');
if ($stmt) {
	$stmt->bind_param(
		"s",
		$id
	);
	$stmt->execute();

	$result = $stmt->get_result();
	if ($result->num_rows === 1) {
		$row = $result->fetch_assoc();

		$name = '';
		$points = 0;
		$opponent = '';
		// Opponent is user2
		if ($row['user1'] == $user) {
			$opponent = $row['user2'];
			$name = $row['first_name2'] . ' ' . $row['last_name2'];
			$points = $row['points2'];
		}
		// Opponent is user1
		else {
			$opponent = $row['user1'];
			$name = $row['first_name1'] . ' ' . $row['last_name1'];
			$points = $row['points1'];
		}

		$final_data['text'] = $final_data['text'] . <<<HTML
<div id="opponent-title">Opponent</div>
<hr class="underline">
HTML;

		$final_data['text'] = $final_data['text'] . '<div>' . $name . ' | ' . $points . ' points' . '</div>';

		if ($row['winner'] == $opponent) {
			$final_data['text'] = $final_data['text'] . <<<HTML
<p style="color: red;">Your opponent has beaten you.<p>
HTML;

		}
	}
	elseif ($result->num_rows === 0) {
		$final_data['left'] = '1';
	}

	$stmt->close();
}

echo json_encode($final_data);

?>
