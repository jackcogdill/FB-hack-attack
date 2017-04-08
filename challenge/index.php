<?php

// Connect to database and start session
$root = $_SERVER["DOCUMENT_ROOT"];
require_once($root . "/secure.php");

$code = '';

// Challenge-specific vars
// (later, maybe retrieve from database)
$chall_info = 'Write Python code to output the following string:<br>"Hello, World!"';
$out_correct = 'Hello, World!';

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
	$answer = ($out === $out_correct) ? $correct_str : $incorrect_str;
}

?>

<html><head>
	
<style type="text/css">

* {
	font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', 'Helvetica', sans-serif;
}

html, body {
	margin: 0;
	width: 100%;
	height: 100%;
	overflow: hidden;
}

#wrap {
	margin: 0 auto;
	display: block;
}

#challenge-info {
	margin-bottom: 1em;
	font-size: 1.5em;
}

#challenge {
	margin: 5% auto 0;
	display: block;
	width: 50%;
}

#code {
	display: block;
	margin: 0 auto;
	width: 100%;
	height: 40%;
	font-size: 1.25em;
}

#submit {
	margin-top: 1em;
	display: block;
	float: right;
}

#answer {
	margin-top: 2em;
	font-size: 1.5em;
}

.correct {
	color: red;
}

input[type=submit] {
	-webkit-border-radius: 0.5em;
	-moz-border-radius: 0.5em;
	border-radius: 0.5em;
	border: none;
	padding: 0.75em 1.5em;
	background-color: #0099CC;
	color: #fff;
	font-size: 1em;
}

input[type=submit]:hover {
	cursor: pointer;
}

</style>

</head><body>

<div id="wrap">
	<form id="challenge" action="challenge.php" method="post">
		<div id="challenge-info">
			<?php echo $chall_info; ?>
		</div>
		<input type="text" id="code" name="code" autocomplete="off" <?php echo 'value="'.$code.'"'; ?>>
		<input type="submit" id="submit" value="Submit">
		<div id="answer">
			<?php echo $answer; ?>
		</div>
	</form>
</div>

</body></html>
