<?php

$up = "../";
// Connect to database and start session
require_once("../secure.php");

$difficulty    = 0;
$code          = '';
$answer        = '';
$minutes       = 1;
$want_opponent = '';
$language      = '';
$lang_info     = '';
$chall_info    = '';
$challenge_num = 0;
$code          = '';


$match_flag   = isset($_POST['language']); // Match up users instead of display challenge
$chall_flag   = isset($_SESSION['user']['hash_id']); // Display the ongoing challenge
$waiting_flag = isset($_SESSION['user']['waiting']); // Currently waiting
$code_flag    = false;

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
			WHERE language = ? AND opponent IS NULL
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
elseif ($chall_flag) {
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

					$language      = $chall_row['language'];
					$minutes       = $chall_row['minutes'];
					$chall_info    = $chall_row['challenge_info'];
					$out_correct   = $chall_row['correct_out'];
					$challenge_num = $chall_row['challenge_num'];


					$lang_info = $language;

					// Code or other (cypto, etc) (code_flag)
					switch ($language) {
						case 'Python':
						case 'Java':
							$code_flag = true;
							break;
						case 'Crypto':
							$lang_info = 'Cryptography';
							break;
						case 'CTF':
							$lang_info = 'Capture the Flag';
							break;
						default:
							break;
					}

///////////////////////////////////////////////////////////////
//           Determine if user completed challenge
///////////////////////////////////////////////////////////////
	if ($language == 'Java') {
		$hash1 = hash('sha256', $_SESSION['user']['username'] . $challenge_id . $start_time);
		$java_class = 'Ha' . hash('adler32', $hash1);
	}

	// Code
	if (isset($_POST['code'])) {
		$code = $_POST['code'];
	}

	// For CTF 2
	if ($language == 'CTF') {
		switch ($challenge_num) {
			case 2:
				if (!isset($_GET['file'])) {
					header('Location: ../challenge/index.php?file=login.php');
					die("Redirecting");
				}
				break;
			case 3:
			case 4:
				$code = "placeholder";
				break;
			case 6:
				// Answer for ctf 6
				$out_correct = $row['id'];
				break;
			default:
				break;
		}
	}

	if (!empty($code)) {
		// Filter non-ascii characters
		$code = iconv("UTF-8", "ASCII//IGNORE", $code);

		$incorrect_str = 'Sorry, try again';

		// Quick security measures
		function safe_code($str, $lang) {
			global $incorrect_str;
			if (strlen($str) > 1000) {
				$incorrect_str = 'Your input exceeded the maximum character limit';
				return false;
			}

			switch ($lang) {
				case 'Python':
				case 'Java':
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
					break;
				default:
					break;
			}

			return true;
		}

		$out = '';
		if (safe_code($code, $language) !== false) {
			switch ($language) {
				case 'Python':
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
				case 'Crypto':
					$out = $code;
					break;
				case 'CTF':
					switch ($challenge_num) {
						case 3:
							if (isset($_POST['ctf3-age'])) {
								$out_correct = 'ctf3 correct';
								// Make the user win
								if ($_POST['ctf3-age'] == '1337') {
									$out = 'ctf3 correct';
								}
								// Make the user lose
								else {
									$out = 'wrong';
								}
							}
							break;
						case 4:
							$ua = $_SERVER['HTTP_USER_AGENT'];
							$chall_info .= '<br><br><strong>User agent:</strong> ' . $ua;

							$out_correct = 'ctf4 correct';
							// Make the user win
							if ($ua === 'HackAttacks') {
								$out = 'ctf4 correct';
							}
							// Make the user lose
							else {
								$out = 'wrong';
							}
							break;
						default:
							$out = $code;
							break;
					}
					break;
				default:
					break;
			}
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
			elseif ($winner == $opponent) {
				$answer = '<span class="correct">That\'s correct, but your opponent got it first.</span>';
			}
			else {
				$answer = '<span class="correct">Correct! Already got points.</span>';
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

//////////////////////////////////////////////////
if ($chall_flag) {
?>
<link rel="stylesheet" type="text/css" href="../css/challenge.css">
<?php
	if ($code_flag) { /////////////////////////
?>
<!-- Code Mirror files -->
<script src="../codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="../codemirror/lib/codemirror.css">
<link rel="stylesheet" type="text/css" href="../codemirror/theme/tomorrow-night-bright.css">
<script src="../codemirror/mode/python/python.js"></script>
<script src="../codemirror/mode/clike/clike.js"></script>
<?php
	} /////////////////////////
}
//////////////////////////////////////////////////

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
elseif ($chall_flag) {
	$action = '../challenge/index.php';
	if ($lang_info == 'Capture the Flag' && $challenge_num === 2) {
		$action = '../challenge/index.php?file=login.php';
	}
?>

<form id="challenge" action="<?php echo $action; ?>" method="post">
	<div id="language-info">
		<?php echo $lang_info; ?>
	</div>
	<div id="challenge-info">
		<?php echo $chall_info; ?>
	</div>
<?php
// Programming language
if ($code_flag) { //////////////////////////
?>
	<textarea id="code" name="code" autocomplete="off"></textarea>
<?php
} //////////////////////////
// Other (Crypto, etc)
else {
	$value = 'Answer';
	if ($lang_info == 'Capture the Flag') {
		$value = 'Password';

		if ($challenge_num === 1) {
			echo '<!-- Good job! You found the password: "La4NrQCUvbzscKeL" -->';
		}
		elseif ($challenge_num === 3) {
?>
	<select name="ctf3-age">
		<option value="">Age</option>
		<option value="0">0</option>
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12">12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
		<option value="17">17</option>
		<option value="18">18</option>
		<option value="19">19</option>
		<option value="20">20</option>
		<option value="21">21</option>
		<option value="22">22</option>
		<option value="23">23</option>
		<option value="24">24</option>
		<option value="25">25</option>
		<option value="26">26</option>
		<option value="27">27</option>
		<option value="28">28</option>
		<option value="29">29</option>
		<option value="30">30</option>
		<option value="31">31</option>
		<option value="32">32</option>
		<option value="33">33</option>
		<option value="34">34</option>
		<option value="35">35</option>
		<option value="36">36</option>
		<option value="37">37</option>
		<option value="38">38</option>
		<option value="39">39</option>
		<option value="40">40</option>
		<option value="41">41</option>
		<option value="42">42</option>
		<option value="43">43</option>
		<option value="44">44</option>
		<option value="45">45</option>
		<option value="46">46</option>
		<option value="47">47</option>
		<option value="48">48</option>
		<option value="49">49</option>
		<option value="50">50</option>
		<option value="51">51</option>
		<option value="52">52</option>
		<option value="53">53</option>
		<option value="54">54</option>
		<option value="55">55</option>
		<option value="56">56</option>
		<option value="57">57</option>
		<option value="58">58</option>
		<option value="59">59</option>
		<option value="60">60</option>
		<option value="61">61</option>
		<option value="62">62</option>
		<option value="63">63</option>
		<option value="64">64</option>
		<option value="65">65</option>
		<option value="66">66</option>
		<option value="67">67</option>
		<option value="68">68</option>
		<option value="69">69</option>
		<option value="70">70</option>
		<option value="71">71</option>
		<option value="72">72</option>
		<option value="73">73</option>
		<option value="74">74</option>
		<option value="75">75</option>
		<option value="76">76</option>
		<option value="77">77</option>
		<option value="78">78</option>
		<option value="79">79</option>
		<option value="80">80</option>
		<option value="81">81</option>
		<option value="82">82</option>
		<option value="83">83</option>
		<option value="84">84</option>
		<option value="85">85</option>
		<option value="86">86</option>
		<option value="87">87</option>
		<option value="88">88</option>
		<option value="89">89</option>
		<option value="90">90</option>
		<option value="91">91</option>
		<option value="92">92</option>
		<option value="93">93</option>
		<option value="94">94</option>
		<option value="95">95</option>
		<option value="96">96</option>
		<option value="97">97</option>
		<option value="98">98</option>
		<option value="99">99</option>
	</select>
<?php
		}
	}
	if ($lang_info == 'Capture the Flag' && ($challenge_num === 3 || $challenge_num === 4 || $challenge_num === 6)) {}
	elseif ($lang_info == 'Capture the Flag' && $challenge_num === 2 && isset($_GET['file']) && $_GET['file'] === 'password.php') {
		echo 'administrator:h2L2AweW';
	}
	else {
?>
	<input type="text" id="code" name="code" placeholder="<?php echo $value; ?>" spellcheck="false">
<?php
	}
	if ($lang_info == 'Capture the Flag' && $challenge_num === 6) {
		$error = true;
		if (isset($_GET['page'])) {
			switch($_GET['page']) {
				case 'contact.txt':
				case 'home.txt':
					$error = false;
					echo file_get_contents('pages/' . $_GET['page'], false);
					break;
				case '../admin/.htpasswd':
					$error = false;
					echo 'admin:lJ2YAeSC82wf2';
					break;
				default:
					break;
			}
		}
		if ($error === true) {
?>
	<b>Warning:</b> main(pages/$page): failed to open stream: No such file or directory in <b>/home/hackattacks/public_html/challenges/index.php</b> on line <b>137</b>
	<br><br>
	<a href="../challenge/admin">Login</a>
<?php
		}
	}
} //////////////////////////


// Challenges which dont need submit
if ($lang_info == 'Capture the Flag' && $challenge_num === 2 && $_GET['file'] == 'password.php') {}
elseif ($lang_info == 'Capture the Flag' && $challenge_num === 4) {}
else {
?>
	<button type="submit" id="submit">Submit</button>
<?php
}
?>
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
<?php
if ($code_flag) { //////////////////////////
?>
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

<?php
} //////////////////////////
?>

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
