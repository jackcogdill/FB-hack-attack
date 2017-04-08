<?php

$up = "";
// Connect to database and start session
require_once("secure.php");

$code = '';

// Challenge-specific vars
// (later, maybe retrieve from database)
$chall_info = 'Write Python code to output the following string:<br>"Hello, World!"';
$out_correct = 'Hello, World!';
$answer = '';

if (!empty($_POST['code'])) {
	$code = $_POST['code'];
	// echo $code;

	// Filter out any non-ascii characters (security measure)
	preg_replace('/[^a-zA-Z0-9]/', '', $code);

	$file = 'test.py'; // --------> Later change this to have session specific filename
	file_put_contents($file, $code);

	$out = `python $file`;
	// echo $out;

	// Delete $file
	unlink($file);


	$correct_str   = '<span class="correct">Correct! Get 1 point(s)</span>';
	$incorrect_str = 'Sorry, try again';
	// Compare user output with expected output
	$out = trim($out);
	$out_correct = trim($out_correct);
	$answer = '';

	if ($out === $out_correct) {
		$answer = $correct_str;

		// Give 1 point to the user
		$points = $_SESSION['user']['points'];

		// Update session
		$_SESSION['user']['points'] = $_SESSION['user']['points'] + 1;

		// Update database
		$user   = $_SESSION['user']['username'];
		$update = "
			UPDATE users
			SET points = points + 1
			WHERE username = '{$user}'
		";

		$query = mysqli_query($connect, $update);
	}
	else {
		$answer = $incorrect_str;
	}
}



require_once("head_top.php");
?>
<link rel="stylesheet" type="text/css" href="css/challenge.css">
<?php
require_once("head_bottom.php");
require_once("header.php");

?>

<form id="challenge" action="challenge/index.php" method="post">
	<div id="challenge-info">
		<?php echo $chall_info; ?>
	</div>
	<input type="text" id="code" name="code" autocomplete="off"
<?php
echo 'value="'.$code.'"';
?>
	>
	<input type="submit" id="submit" value="Submit">
	<div id="answer">
		<?php echo $answer; ?>
	</div>
</form>

<?php

require_once("footer.php");

?>
