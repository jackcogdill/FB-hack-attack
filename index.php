<?php
$up = "";
require_once("secure.php");
require_once("head_top.php");
?>

<link rel="stylesheet" href="css/home.css">

<?php
require_once("head_bottom.php");
require_once("header.php");

// Reset any matches
// =====================
// Ongoing
$user = $_SESSION['user']['username'];
$delo_stmt = $connect->prepare('
	DELETE FROM ongoing
	WHERE (
		user1 = ? OR
		user2 = ?
	)
');
if ($delo_stmt) {
	$delo_stmt->bind_param(
		"ss",
		$user,
		$user
	);
	$delo_stmt->execute();
	$delo_stmt->close();
}

// Delete hash id
if (isset($_SESSION['user']['hash_id'])) {
	unset($_SESSION['user']['hash_id']);
}

// Waiting
$delw_stmt = $connect->prepare('
	DELETE FROM waiting
	WHERE username = ?
');
if ($delw_stmt) {
	$delw_stmt->bind_param(
		"s",
		$user
	);
	$delw_stmt->execute();
	$delw_stmt->close();
}

// Delete from waiting
if (isset($_SESSION['user']['waiting'])) {
	unset($_SESSION['user']['waiting']);
}
// End reset matches
// =====================

$bad_notice = '';
if (isset($_GET['osu'])) {
	$bad_notice = 'You cannot challenge yourself.';
}
else if (isset($_GET['udne'])) {
	$bad_notice = 'The user you challenged does not exist.';
}
else if (isset($_GET['nod'])) {
	$bad_notice = 'Please select a difficulty.';
}

?>

<p class="logo"> Hack Attack </p>
<hr class="underline">
<form action="challenge/index.php" method="post">
<div id="button-wrap">
	<div>Java&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
		</select>
		<button type="submit" name="language" class="java" value="Java"></button>
	</div>
	<div>Python&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
		</select>
		<button type="submit" name="language" class="python" value="Python"></button>
	</div>
	<div id="choose-opponent">
		<div>
<?php
if (!empty($bad_notice)) {
?>
<div class="badnotice">
<?php echo $bad_notice; ?>
</div>
<?php
}
?>
			Opponent's username:
			<input type="text" name="specific-opponent" placeholder="Random" onkeydown="if (event.keyCode == 13) return false;">
		</div>
	</div>
	<div>Crypto&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
		</select>
		<button type="submit" name="language" class="crypto" value="Crypto"></button>
	</div>
	<div>CTF&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
		</select>
		<button type="submit" name="language" class="ctf" value="CTF"></button>
	</div>
</div>
</form>

<?php
require_once("footer.php");
?>
