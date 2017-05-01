<?php

require_once("../../secure.php");

// Answer for ctf 6
$value = '';
if (isset($_SESSION['user']['hash_id'])) {
	$value = $_SESSION['user']['hash_id'];
}

?>

<form id="submit-this" action="/challenge" method="post">
	<input type="hidden" name="code" value="<?php echo $value; ?>">
</form>

<script>
document.getElementById("submit-this").submit();
</script>