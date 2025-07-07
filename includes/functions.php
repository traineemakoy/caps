<?php
function logActivity($conn, $user_id, $user_type, $activity) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $user_type, $activity);
    $stmt->execute();
}
?>
