<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faculty_number'])) {
    $faculty_number = $_POST['faculty_number'];

    // Build Flask API URL
    $flask_url = "http://localhost:5000/enroll?faculty_number=" . urlencode($faculty_number);

    // Make HTTP GET request to Flask
    $response = file_get_contents($flask_url);

    // Optional: Save response to text file so PHP UI can show status
    $status_path = "latest_fp_status.txt";
    file_put_contents($status_path, $response);

    // Redirect back to enroll page
    
header("Location: registrar_dashboard.php?page=enroll_fingerprint");
exit();

} else {
    echo "Invalid request.";
}
