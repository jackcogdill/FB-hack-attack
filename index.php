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
$select = "
	DELETE FROM ongoing
	WHERE (
		user1 = '{$user}' OR
		user2 = '{$user}'
	)
";
$query = mysqli_query($connect, $select);

if (isset($_SESSION['user']['hash_id'])) {
	unset($_SESSION['user']['hash_id']);
}

// Waiting
$select = "
	DELETE FROM waiting
	WHERE username = '{$user}'
";
$query = mysqli_query($connect, $select);

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

?>

<p class="logo"> Hack Attack </p>
<hr class="underline">
<form action="challenge/index.php" method="post">
<ul id="button-wrap">
	<li>Java&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
		</select>
		<button type="submit" name="language" class="java" value="Java"></button>
	</li>
	<li>Python&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
		</select>
		<button type="submit" name="language" class="python" value="Python"></button>
	</li>
	<li>Crypto&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
		</select>
		<button type="submit" name="language" class="crypto" value="Crypto"></button>
	</li>
	<li>CTF&nbsp;
		<select class="difficulty" name="difficulty[]">
			<option value="">Lvl</option>
			<option value="1">1</option>
		</select>
		<button type="submit" name="language" class="ctf" value="CTF"></button>
	</li>
</ul>
</form>

<?php
require_once("footer.php");
?>
