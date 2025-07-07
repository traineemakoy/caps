<?php
include('../includes/db.php');
include('../includes/log_module.php');
session_start();

$registrar_id = $_SESSION['registrar_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pending_id'])) {
    $pending_id = $_POST['pending_id'];

    // 🔍 Get student info from pending list
    $get = $conn->prepare("SELECT * FROM pending_students WHERE pending_id = ?");
    $get->bind_param("i", $pending_id);
    $get->execute();
    $student = $get->get_result()->fetch_assoc();

    if ($student) {
        // 📝 Prepare INSERT to official students table
        $insert = $conn->prepare("INSERT INTO students (student_number, first_name, last_name, middle_name, password, course, year_level, section, transferee, irregular, enrollment_receipt, phone_number, email, term)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insert->bind_param(
            "ssssssssssssss",
            $student['student_number'],
            $student['first_name'],
            $student['last_name'],
            $student['middle_name'],
            $student['password'],
            $student['course'],
            $student['year_level'],
            $student['section'],
            $student['transferee'],
            $student['irregular'],
            $student['enrollment_receipt'],
            $student['phone_number'],
            $student['email'],
            $student['term']
        );

        if ($insert->execute()) {
            // 🗑️ Delete from pending list
            $del = $conn->prepare("DELETE FROM pending_students WHERE pending_id = ?");
            $del->bind_param("i", $pending_id);
            $del->execute();

            // ✅ Log success
            log_activity_action($conn, "Approved pending student: " . $student['student_number']);

            $_SESSION['alert'] = "✅ Student approved and moved to official list.";
        } else {
            // ❌ Log failure if insert fails
            log_activity_action($conn, "❌ Failed to approve student: " . $student['student_number']);
        }
    }
}

// 🔁 Redirect back to pending students page
header("Location: registrar_dashboard.php?page=pending_students");
exit();
