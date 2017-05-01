<div id="header">
<?php

require_once($up . "secure.php");

$name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$pts  = $_SESSION['user']['points'];

/////////////////////////////////
if (trim($up) !== '') {
?>
	<a class="logo" href=<?php echo '"'. $up .'"'; ?>><div> Hack<br>Attack </div></a>
<?php
}
/////////////////////////////////
?>
	<div id="head-non-logo">
		<div id="user-info">
			<?php echo $name . ' | ' . $pts . " points";?>
		</div>
<?php
if (strpos($_SERVER['REQUEST_URI'], 'challenge') !== false) {
?>
	<a href="<?php echo $up . 'logout'; ?>" onclick="return confirm('Are you sure you want to logout?\nYou will leave the game.')">Logout</a>
<?php
}
else {
?>
	<a href="<?php echo $up . 'logout'; ?>">Logout</a>
<?php
}
?>
	</div>
</div>

<div id="wrap">
