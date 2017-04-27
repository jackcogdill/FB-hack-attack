<?php
require_once('db.php');

if (!isset($_SESSION['user']['hash_id'])) {
	die();
}

$user = $_SESSION['user']['username'];
$id   = $_SESSION['user']['hash_id'];
$select = "
	SELECT *
	FROM ongoing
	WHERE id = '{$id}'
";

$query = mysqli_query($connect, $select);
$rows  = mysqli_num_rows($query);

if ($rows == 1) {
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

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
?>
<div id="opponent-title">Opponent</div>
<hr class="underline">
<div><?php echo $name . ' | ' . $points . ' points'; ?></div>
<?php

if ($row['winner'] == $opponent) {
	echo '<p style="color: red;">Your opponent has beaten you.<p>';
}

}

?>
