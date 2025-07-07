<?php
// ✅ 1. Generic function to insert log with full info
function log_activity($conn, $user_fullname, $user_type, $activity) {
    $stmt = $conn->prepare("INSERT INTO audit_trail (user_fullname, user_type, activity) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user_fullname, $user_type, $activity);
    $stmt->execute();
}

// ✅ 2. AUTO-DETECT full_name and user_type via session (used in most logging)
function log_activity_action($conn, $action_desc) {
    if (session_status() == PHP_SESSION_NONE) session_start();

    $user_fullname = $_SESSION['full_name'] ?? $_SESSION['registrar_name'] ?? $_SESSION['faculty_name'] ?? 'Unknown';

    // Auto detect user type based on session content
    if (isset($_SESSION['username']) && strpos($_SESSION['username'], '@') === 0) {
        $user_type = 'registrar';
    } elseif (isset($_SESSION['faculty_number'])) {
        $user_type = 'faculty';
    } else {
        $user_type = 'unknown';
    }

    log_activity($conn, $user_fullname, $user_type, $action_desc);
}

// ✅ 3. logThis() – accepts user_id but still uses session to identify name/type
function logThis($conn, $user_id, $action_desc) {
    if (session_status() == PHP_SESSION_NONE) session_start();

    // Fallback to session values
    $user_fullname = $_SESSION['full_name'] ?? $_SESSION['registrar_name'] ?? $_SESSION['faculty_name'] ?? 'Unknown';

    if (isset($_SESSION['username']) && strpos($_SESSION['username'], '@') === 0) {
        $user_type = 'registrar';
    } elseif (isset($_SESSION['faculty_number'])) {
        $user_type = 'faculty';
    } else {
        $user_type = 'unknown';
    }

    log_activity($conn, $user_fullname, $user_type, $action_desc);
}
