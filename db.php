<?php
date_default_timezone_set("Asia/Calcutta");
$prod = true;
if(!$prod){
    $sname = "localhost";
    $uname = "u713144528_lms";
    $password = "Lms@7131";
    $db_name = "u713144528_lms";
    echo "hi";
}
else{
    $sname = "localhost";
    $uname = "root";
    $password = "root";
    $db_name = "mysareewebpage";
}


$conn = mysqli_connect($sname, $uname, $password, $db_name);

if (!$conn) {
	echo "Connection Failed!";
	exit();
}
$currency = "$";
$bz = "LMS";
$title = "vijayalaxmi sarees";
$logo = "";

$logoutLimit = 10;
$time = date('H:i');
$date = date('Y-m-d');
$datetime = date('Y-m-d');
$month = date('Y-m');

