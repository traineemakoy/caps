<?php
include('../includes/db.php');

$faculty_number = $_POST['faculty_number'] ?? '';
$fingerprint_id = $_POST['fingerprint_id'] ?? '';

if (!$faculty_number || !$fingerprint_id) {
    http_response_code(400);
    exit("Missing data.");
}

// Insert into DB
$insert = mysqli_query($conn, "
    INSERT INTO faculty_fingerprint (faculty_number, fingerprint_id)
    VALUES ('$faculty_number', '$fingerprint_id')
");

echo $insert ? "success" : "failed";
