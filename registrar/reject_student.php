<?php
include('../includes/db.php');
include('../includes/log_module.php');
session_start();

$registrar_id = $_SESSION['registrar_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pending_id'])) {
    $pending_id = $_POST['pending_id'];

    // 🧠 Get student info for logging before deletion
    $get = $conn->prepare("SELECT student_number, first_name, last_name FROM pending_students WHERE pending_id = ?");
    $get->bind_param("i", $pending_id);
    $get->execute();
    $res = $get->get_result();
    $student = $res->fetch_assoc();

    $student_number = $student['student_number'] ?? 'Unknown';
    $full_name = ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '');

    // 🗑 Delete the student
    $del = $conn->prepare("DELETE FROM pending_students WHERE pending_id = ?");
    $del->bind_param("i", $pending_id);
    $del->execute();

    // 📝 Log activities
    log_activity_action($conn, "Rejected Pending Student: $student_number — $full_name");
    logThis($conn, $registrar_id, "Rejected pending student: $full_name ($student_number)");

    $_SESSION['alert'] = "❌ Student rejected.";
}

header("Location: registrar_dashboard.php?page=pending_students");
exit();
