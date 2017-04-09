<?php
$up = "";
require_once("secure.php");
require_once("head_top.php");
?>

<link rel="stylesheet" href="css/home.css">

<?php
require_once("head_bottom.php");
require_once("header.php");
?>

<p id="logo"> Hack Attack </p>
<hr class="underline">
<form action="challenge/index.php" method="post">
<ul id="button-wrap">
	<li><button type="submit" name="language" class="java" value="Java">Java</button></li>
	<li><button type="submit" name="language" class="python" value="Python">Python</button></li>
	<li><button type="submit" name="language" class="c" value="C">C</button></li>
	<li><button type="submit" name="language" class="js" value="Javascript">Javascript</button></li>
	<li><button type="submit" name="language" class="ruby" value="Ruby">Ruby</button></li>
	<li><button type="submit" name="language" class="php" value="PHP">PHP</button></li>
</ul>
</form>

<?php
require_once("footer.php");
?>
