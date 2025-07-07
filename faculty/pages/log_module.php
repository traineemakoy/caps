<?php
function logFacultyActivity($conn, $faculty_number, $activity) {
    $stmt = $conn->prepare("INSERT INTO faculty_logs (faculty_number, activity, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $faculty_number, $activity);
    $stmt->execute();
}
?>
