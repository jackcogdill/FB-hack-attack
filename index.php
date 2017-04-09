<?php
$up = "";
require_once("secure.php");
require_once("head_top.php");
?>

<link rel="stylesheet" href="css/home.css">

<?php
require_once("head_bottom.php");
require_once("header.php");

// Reset any matche
if (isset($_SESSION['user']['hash_id'])) {
	$id = $_SESSION['user']['hash_id'];
	$select = "
		DELETE FROM ongoing
		WHERE id = '{$id}'
		LIMIT 1
	";
	$query = mysqli_query($connect, $select);

	if (isset($_SESSION['user']['hash_id'])) {
		unset($_SESSION['user']['hash_id']);
	}
	if (isset($_SESSION['user']['waiting'])) {
		unset($_SESSION['user']['waiting']);
	}
}

?>

<p id="logo"> Hack Attack </p>
<hr class="underline">
<form action="challenge/index.php" method="post">
<ul id="button-wrap">
	<li>Java<button type="submit" name="language" class="java" value="Java"></button></li>
	<li>Python&nbsp;
<select id="difficulty" name="difficulty">
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
</select>
<button type="submit" name="language" class="python" value="Python"></button></li>
	<li>C<button type="submit" name="language" class="c" value="C"></button></li>
	<li>Javascript<button type="submit" name="language" class="js" value="Javascript"></button></li>
	<li>Ruby<button type="submit" name="language" class="ruby" value="Ruby"></button></li>
	<li>PHP<button type="submit" name="language" class="php" value="PHP"></button></li>
</ul>
</form>

<?php
require_once("footer.php");
?>
