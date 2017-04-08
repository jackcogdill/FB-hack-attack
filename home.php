<?php
session_start();
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
echo $first_name. " ".$last_name;
?>