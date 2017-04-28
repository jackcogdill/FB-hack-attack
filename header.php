<div id="header">
<?php

require_once($up . "secure.php");

$name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$pts  = $_SESSION['user']['points'];

/////////////////////////////////
if (trim($up) !== '') {
?>
	<a class="logo" href=<?php echo '"'. $up .'"'; ?>><p> Hack<br>Attack </p></a>
<?php
}
/////////////////////////////////
?>
	<div id="head-non-logo">
		<div id="user-info">
			<?php echo $name . ' | ' . $pts . " points";?>
		</div>
		<a href=<?php echo $up . "logout"; ?>>Logout</a>
	</div>
</div>

<div id="wrap">
