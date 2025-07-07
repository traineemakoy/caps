<?php
session_start();
include("includes/db.php");

$username = $_SESSION['username'] ?? null;
$session_id = $_SESSION['session_log_id'] ?? null;

if ($username) {
    if ($session_id) {
        // ✅ Safer tracking by session ID
        $update = $conn->prepare("UPDATE login_analytics SET logout_time = NOW(), session_duration = TIMEDIFF(NOW(), login_time) WHERE id = ?");
        $update->bind_param("i", $session_id);
        $update->execute();
    } else {
        // Optional fallback (latest login)
        $stmt = $conn->prepare("SELECT id, login_time FROM login_analytics WHERE username = ? ORDER BY login_time DESC LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $login_time = new DateTime($row['login_time']);
            $logout_time = new DateTime();
            $interval = $login_time->diff($logout_time);
            $duration = $interval->format('%H:%I:%S');

            $update = $conn->prepare("UPDATE login_analytics SET logout_time = NOW(), session_duration = ? WHERE id = ?");
            $update->bind_param("si", $duration, $row['id']);
            $update->execute();
        }
    }
}

// Destroy session
session_unset();
session_destroy();
header("Location: index.php");
exit();
