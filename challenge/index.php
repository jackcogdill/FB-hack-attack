<?php

$up = "../";
// Connect to database and start session
require_once("../secure.php");

$difficulty = 0;
$code = '';
$answer = '';
$minutes = 1;
$want_opponent = '';
$language = '';


$match_flag   = isset($_POST['language']); // Match up users instead of display challenge
$chall_flag   = isset($_SESSION['user']['hash_id']); // Display the ongoing challenge
$waiting_flag = isset($_SESSION['user']['waiting']); // Currently waiting

if (isset($_POST['difficulty'])) {
	foreach($_POST['difficulty'] as $value) {
		if (!empty($value)) {
			$difficulty = $value;
		}
	}
	if ($difficulty === 0) {
		header('Location: ../index.php?nod');
		die("Redirecting");
	}
}

if ($match_flag) {
	// Language
	$language = $_POST['language'];

	// Try to match with this opponent (wait for them to join you)
	$want_opponent = trim($_POST['specific-opponent']);

	// Cannot challenge yourself
	if ($want_opponent === $_SESSION['user']['username']) {
		header('Location: ../index.php?osu');
		die("Redirecting");
	}

	$query = '';
	// Looking for specific opponent
	if (!empty($want_opponent)) {
		// Make sure user exists
		////////////////////////
		$ue_stmt = $connect->prepare('
			SELECT username
			FROM users
			WHERE username = ?
		');
		if ($ue_stmt) {
			$ue_stmt->bind_param(
				"s",
				$want_opponent
			);
			$ue_stmt->execute();

			$result = $ue_stmt->get_result();
			if ($result->num_rows === 0) {
				header('Location: ../index.php?udne');
				die("Redirecting");
			}

			$ue_stmt->close();
		}
		////////////////////////

		$query = '
			SELECT *
			FROM waiting
			WHERE username = ? AND opponent = ?
			LIMIT 1
		';
	}
	// Random
	else {
		$query = '
			SELECT *
			FROM waiting
			WHERE language = ? AND opponent != NULL
			LIMIT 1
		';
	}

	// Prepare
	$stmt = $connect->prepare($query);
	if ($stmt) {
		// Bind parameters
		// =======================
		// Looking for specific opponent
		// and opponent is looking specifically for you
		if (!empty($want_opponent)) {
			$stmt->bind_param(
				"ss",
				$want_opponent,
				$_SESSION['user']['username']
			);
		}
		// Random
		else {
			$stmt->bind_param(
				"s",
				$language
			);
		}

		$stmt->execute();

		// Get number of rows
		$result = $stmt->get_result();

		// If no one is waiting with your language,
		// or your opponent is not waiting:
		// get added to waiting list
		if ($result->num_rows === 0) {
			$wait_query = '';
			if (!empty($want_opponent)) {
				$wait_query = '
					INSERT INTO waiting
						(username, first_name, last_name, language, points, opponent)
					VALUES (?, ?, ?, ?, ?, ?)
				';
			}
			else {
				// Keep opponent as null
				$wait_query = '
					INSERT INTO waiting
						(username, first_name, last_name, language, points)
					VALUES (?, ?, ?, ?, ?)
				';
			}

			$wait_stmt = $connect->prepare($wait_query);
			if ($wait_stmt) {
				if (!empty($want_opponent)) {
					$wait_stmt->bind_param(
						"ssssis",
						$_SESSION['user']['username'],
						$_SESSION['user']['first_name'],
						$_SESSION['user']['last_name'],
						$language,
						$_SESSION['user']['points'],
						$want_opponent
					);
				}
				// Keep opponent as null
				else {
					$wait_stmt->bind_param(
						"ssssi",
						$_SESSION['user']['username'],
						$_SESSION['user']['first_name'],
						$_SESSION['user']['last_name'],
						$language,
						$_SESSION['user']['points']
					);
				}

				$wait_stmt->execute();
				$wait_stmt->close();
			}

			$_SESSION['user']['waiting'] = 1;
			$waiting_flag = 1;
		}
		// Get matched with opponent:
		// both users get added to ongoing challenges
		// (waiting person is deleted from waiting)
		elseif ($result->num_rows === 1) {
			$row = $result->fetch_assoc(); // Get row

			$ongo_stmt = $connect->prepare('
				INSERT INTO ongoing
					(
						id,
						challenge_id,
						user1,
						user2,
						first_name1,
						last_name1,
						first_name2,
						last_name2,
						start_time,
						points1,
						points2
					)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			');
			if ($ongo_stmt) {
				$user1 = $row['username'];
				$user2 = $_SESSION['user']['username'];

				if ($user1 == $user2) {
					header('Location: ../index.php?osu');
					die('Redirecting');
				}

				$start_time = time();

				// Get challenge id
				// =======================
				$challenge_id = 0;
				$ci_stmt = $connect->prepare('
					SELECT id
					FROM challenges
					WHERE
						language = ? AND challenge_num = ?
					LIMIT 1
				');
				if ($ci_stmt) {
					$ci_stmt->bind_param(
						"si",
						$language,
						$difficulty
					);
					$ci_stmt->execute();

					$ci_result = $ci_stmt->get_result();
					if ($ci_result->num_rows === 1) {
						$ci_row = $ci_result->fetch_assoc();
						$challenge_id = $ci_row['id']; // Got challenge id
					}

					$ci_stmt->close();
				}
				// End get challenge id
				// =======================

				$hash_id = hash('sha512', $user1 . $user2 . $start_time);

				$ongo_stmt->bind_param(
					"sissssssiii",
					$hash_id,
					$challenge_id,
					$user1,
					$user2,
					$row['first_name'],
					$row['last_name'],
					$_SESSION['user']['first_name'],
					$_SESSION['user']['last_name'],
					$start_time,
					$row['points'],
					$_SESSION['user']['points']
				);
				$ongo_stmt->execute();
				$ongo_stmt->close();

				// Delete user1 from waiting list
				$del_stmt = $connect->prepare('
					DELETE FROM waiting
					WHERE username = ?
				');
				if ($del_stmt) {
					$del_stmt->bind_param(
						"s",
						$user1
					);
					$del_stmt->execute();
					$del_stmt->close();
				}

				$_SESSION['user']['hash_id'] = $hash_id;
				header('Location: index.php');
				die('Redirecting');
			}
		}

		$stmt->close();
	}
}
else if ($chall_flag) {
	$lang_info = $language;

	$id = $_SESSION['user']['hash_id'];
	$sel_stmt = $connect->prepare('
		SELECT *
		FROM ongoing
		WHERE id = ?
		LIMIT 1
	');
	if ($sel_stmt) {
		$sel_stmt->bind_param(
			"s",
			$id
		);
		$sel_stmt->execute();

		$result = $sel_stmt->get_result();
		if ($result->num_rows === 1) {
			$row = $result->fetch_assoc(); // Get row


			$start_time = $row['start_time'];
			$winner = $row['winner'];

			$opponent = NULL;
			$user   = $_SESSION['user']['username'];
			// Opponent is user2
			if ($row['user1'] == $user) {
				$opponent = $row['user2'];
			}
			// Opponent is user1
			else {
				$opponent = $row['user1'];
			}

			$challenge_id = $row['challenge_id'];


			$chall_stmt = $connect->prepare('
				SELECT *
				FROM challenges
				WHERE id = ?
				LIMIT 1
			');
			if ($chall_stmt) {
				$chall_stmt->bind_param(
					"s",
					$challenge_id
				);
				$chall_stmt->execute();

				$chall_result = $chall_stmt->get_result();
				if ($chall_result->num_rows === 1) {
					$chall_row = $chall_result->fetch_assoc(); // Get row

					$language    = $chall_row['language'];
					$minutes     = $chall_row['minutes'];
					$chall_info  = $chall_row['challenge_info'];
					$out_correct = $chall_row['correct_out'];

///////////////////////////////////////////////////////////////
//           Determine if user completed challenge
///////////////////////////////////////////////////////////////
	if ($language == 'Java') {
		$hash1 = hash('sha256', $_SESSION['user']['username'] . $challenge_id . $start_time);
		$java_class = 'Ha' . hash('adler32', $hash1);
	}

	if (!empty($_POST['code'])) {
		$code = $_POST['code'];

		// Filter non-ascii characters
		$code = iconv("UTF-8", "ASCII//IGNORE", $code);

		$incorrect_str = 'Sorry, try again';

		// Quick security measures
		function safe_code($str) {
			global $incorrect_str;
			if (strlen($str) > 1000) {
				$incorrect_str = 'Your code exceeded the maximum character limit';
				return false;
			}

			$pos = strrpos(strtolower($str), 'import');
			if ($pos !== false) {
				$incorrect_str = 'You may not use import';
				return false;
			}

			$pos = strrpos(strtolower($str), 'exec');
			if ($pos !== false) {
				$incorrect_str = 'You may not use exec';
				return false;
			}

			$pos = strrpos(strtolower($str), 'eval');
			if ($pos !== false) {
				$incorrect_str = 'You may not use eval';
				return false;
			}

			return true;
		}

		$out = '';
		switch ($language) {
			case 'Python':
				if (safe_code($code) === false) { break; }

				// Needs chmod 777 challenge
				$file = getcwd() . '/' . hash('md5', $_SESSION['user']['username'] . time()) . 'test.py';
				file_put_contents($file, $code);

				$dir = getcwd();
				// Only allow user scripts to run specific time amount
				// E.g., 0.5 seconds only
				// After that, kill the process
				// This is to protect against infinite loops that will crash the server
				$cmd = 'python \''.$file.'\'';
				$out = `python '{$dir}/run.py' 0.5 {$cmd}`;

				// Delete $file
				unlink($file);
				break;
			case 'Java':
				if (safe_code($code) === false) { break; }

				$file = getcwd() . '/' . hash('md5', $_SESSION['user']['username'] . time()) . 'test.java';
				file_put_contents($file, $code);

				$dir = getcwd();
				// Compile java code
				$cmd = 'javac -d \''.$dir.'\' \''.$file.'\'';
				$out = `python '{$dir}/run.py' 1.5 {$cmd}`;

				// Run java code
				$cmd = 'java ' . $java_class;
				$out = `python '{$dir}/run.py' 0.5 {$cmd}`;

				// Delete files
				unlink($file);
				unlink(getcwd() . '/' . $java_class . '.class');
				break;
			default:
				break;
		}

		// Compare user output with expected output
		$out = trim($out);
		$out_correct = trim($out_correct);
		$answer = '';

		if ($out === $out_correct) {
			if ($winner == NULL) {
				// Give points to the user
				// =============================
				$chall_points = $chall_row['points'];

				// Update session
				$_SESSION['user']['points'] = $_SESSION['user']['points'] + $chall_points;

				// Update points in database
				$pnt_stmt = $connect->prepare('
					UPDATE users
					SET points = points + ?
					WHERE username = ?
				');
				if ($pnt_stmt) {
					$pnt_stmt->bind_param(
						"is",
						$chall_points,
						$user
					);
					$pnt_stmt->execute();
					$pnt_stmt->close();
				}

				// Update database for winner
				$win_stmt = $connect->prepare('
					UPDATE ongoing
					SET winner = ?
					WHERE id = ?
				');
				if ($win_stmt) {
					$win_stmt->bind_param(
						"ss",
						$user,
						$id
					);
					$win_stmt->execute();
					$win_stmt->close();
				}

				// Update answer string with correct points
				$answer = '<span class="correct">Correct! Get '. $chall_points .' point(s)</span>';
			}
			else if ($winner == $opponent) {
				$answer = '<span class="correct">Sorry, you\'ve been beaten by your opponent.</span>';
			}
		}
		else {
			$answer = '<span class="correct">'. $incorrect_str .'</span>';
		}
	}

	// Code was empty, but Java code so setup class
	elseif ($language == 'Java') {
		$code = 'class '.$java_class.' {
	public static void main(String[] args) {

	}
}';
	}
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

				}

				$chall_stmt->close();
			}
		}

		$sel_stmt->close();
	}
}



require_once("../head_top.php");

/////////////////////////
if ($chall_flag) {
?>
<link rel="stylesheet" type="text/css" href="../css/challenge.css">

<!-- Code Mirror files -->
<script src="../codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="../codemirror/lib/codemirror.css">
<link rel="stylesheet" type="text/css" href="../codemirror/theme/tomorrow-night-bright.css">
<script src="../codemirror/mode/python/python.js"></script>
<script src="../codemirror/mode/clike/clike.js"></script>

<script type="text/javascript">
function submit() {

}
</script>

<?php
}
/////////////////////////

require_once("../head_bottom.php");
require_once("../header.php");

//////////////////////////////////////////////////
if ($waiting_flag) {
?>

<style>
img.loading {
	margin: 10% auto 0;
	display: block;
}
div.desc {
	margin: 0 auto;
	width: 50%;
	display: block;
	text-align: center;
	font-size: 1.2em;
}
</style>

<img src='../images/loading.gif' class='loading'/>
<div class='desc'>
<?php
if (!empty($want_opponent)) {
	echo 'Waiting for ' . $want_opponent . ' to challenge you back.';
}
else {
	echo 'Please wait while we match you with another coder.';
}
?>
</div>

<script type="text/javascript">
function redirect() {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '../link.php', true);
	xhr.onreadystatechange = function() {
		if(xhr.readyState == 4 && xhr.status == 200 && xhr.responseText !== '') {
			window.location = xhr.responseText;
		}
	}
	xhr.send();
}

// Check every second
window.setInterval(redirect, 500);
</script>

<?php
}
//////////////////////////////////////////////////
else if ($chall_flag) {
?>

<form id="challenge" action="../challenge/index.php" method="post">
	<div id="language-info">
		<?php echo $lang_info; ?>
	</div>
	<div id="challenge-info">
		<?php echo $chall_info; ?>
	</div>
	<textarea id="code" name="code" autocomplete="off"></textarea>
	<input type="submit" id="submit" value="Submit">
	<div id="answer">
		<?php echo $answer; ?>
	</div>
</form>
<div id="opponent"></div>
<div id="timer"></div>

<script>
var mins = <?php echo $minutes; ?>;
var ms = mins * 60 * 1000;
var countDownDate = (<?php echo $start_time; ?> * 1000) + ms;

var x = setInterval(function() {

	var now = new Date().getTime();

	var distance = countDownDate - now;

	var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
	var seconds = Math.floor((distance % (1000 * 60)) / 1000);

	var str = "" + seconds;
	var pad = "00";
	var ans = pad.substring(0, pad.length - str.length) + str;
	document.getElementById("timer").innerHTML = minutes + ":" + ans;
	document.getElementById("timer").style.opacity = 1;

	if (distance < 0) {
		clearInterval(x);
		document.getElementById("timer").innerHTML = "0:00";
		alert('Returning to home page');
		window.location = '../index.php';
	}
}, 1000);
</script>

<script type="text/javascript">
var editor = CodeMirror.fromTextArea(document.getElementById('code'), {
	mode:<?php
		switch($language) {
			case 'Python':
				echo "'python'";
				break;
			case 'Java':
				echo "'text/x-java'";
				break;
		}
	?>,
	lineNumbers: true,
    styleActiveLine: true,
    theme: 'tomorrow-night-bright'
});

// Set the code inside the editor
editor.getDoc().setValue(<?php

$code = addslashes($code);
$code = str_replace(array("\r","\n"),array("\\r","\\n"), $code);
echo '"'.$code.'"';

?>);

function opponent_refresh() {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '../opponent.php', true);
	xhr.onreadystatechange = function() {
		if(xhr.readyState == 4 && xhr.status == 200 && xhr.responseText !== '') {
			document.getElementById('opponent').innerHTML = xhr.responseText;
		}
	}
	xhr.send();
}

// Check every second
window.setInterval(opponent_refresh, 1000);
</script>

<?php
}
//////////////////////////////////////////////////

require_once("../footer.php");

?>
