<div id="header">
<?php

require_once("secure.php");

$name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$pts  = $_SESSION['user']['points'];
?>
	<div>
		<?php echo $name;?>
	</div>
	<div>
		<?php echo $pts . " points";?>
	</div>
	<a href="logout">Logout</a>
</div>

<div id="wrap">
