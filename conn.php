<?php
$servername = "localhost";
$username = "root"; 
$password = "root";
$dbname = "mysareewebpage";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
global $conn;

include_once('repository.php');

$sareesRepo = new Repository('sarees','id',$conn);


?>