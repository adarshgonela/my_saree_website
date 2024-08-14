<?php
include_once 'db.php'; // Ensure this file correctly sets up the $conn variable

// Retrieve form data
$sareetype = $_REQUEST['sareestype'];
$sareesmrp = $_REQUEST['sareesmrp'];
$sareesprice = $_REQUEST['sareesprice'];
$sareesquantity = $_REQUEST['sareesquantity'];

// Function to handle file uploads
function getFileContent($fileKey) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES[$fileKey]['tmp_name'];
        return file_get_contents($fileTmpName);
    }
    return null;
}

// Get file contents
$i1 = getFileContent('img1');
$i2 = getFileContent('img2');
$i3 = getFileContent('img3');
$i4 = getFileContent('img4');
$i5 = getFileContent('img5');

// Set timezone and get current datetime
date_default_timezone_set("Asia/Kolkata");
$current_datetime = date("Y-m-d H:i:s");

// Prepare SQL query
$sql = "INSERT INTO sarees (sareetype, sareesmrp, sareesprice, sareesquantity, img1, img2, img3, img4, img5, date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare and bind
$statement = $conn->prepare($sql);
if ($statement === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters - 'b' for blob (binary data)
$statement->bind_param("ssssssssss", $sareetype, $sareesmrp, $sareesprice, $sareesquantity, $i1, $i2, $i3, $i4, $i5, $current_datetime);

// Execute statement
if ($statement->execute()) {
    echo "New record created successfully";
} else {
    echo "Execute failed: " . $statement->error;
}

// Close statement and connection
$statement->close();
$conn->close();
?>
