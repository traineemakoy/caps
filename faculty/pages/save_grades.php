<?php
session_start();
include(__DIR__ . "/../../includes/db.php");
include(__DIR__ . "/../../includes/log_module.php");
include("log_module.php");

$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grades'])) {
    $student_number = $_POST['student_number'];
    $gradesData = $_POST['grades'];

    // 🔎 Get student name
    $stmt = $conn->prepare("SELECT first_name, last_name FROM students WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $full_name = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Unknown Student';

    foreach ($gradesData as $subject_id => $data) {
        // ✅ Allow blanks and only calculate with available values
        $prelim = $data['prelim'] !== '' ? floatval($data['prelim']) : null;
        $midterm = $data['midterm'] !== '' ? floatval($data['midterm']) : null;
        $finals = $data['finals'] !== '' ? floatval($data['finals']) : null;
        $clearance_status = $data['clearance'];

        // 📊 Calculate average only from non-null grades
        $grade_parts = array_filter([$prelim, $midterm, $finals], fn($v) => $v !== null);
        $grade = count($grade_parts) > 0 ? round(array_sum($grade_parts) / count($grade_parts), 2) : null;

        // 📝 Remarks
        if ($grade !== null) {
            $remarks = ($grade >= 1.0 && $grade <= 3.0) ? 'Pass' : (($grade > 3.0 && $grade <= 5.0) ? 'Failed' : '');
        } else {
            $remarks = '';
        }

        // 📘 Get subject name
        $sub_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE subject_id = ?");
        $sub_stmt->bind_param("i", $subject_id);
        $sub_stmt->execute();
        $sub_res = $sub_stmt->get_result();
        $subject = $sub_res->fetch_assoc();
        $subject_name = $subject ? $subject['subject_name'] : 'Unknown Subject';

        // 📌 Check existing grade
        $check = $conn->prepare("SELECT * FROM grades WHERE student_number = ? AND subject_id = ?");
        $check->bind_param("si", $student_number, $subject_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $update = $conn->prepare("UPDATE grades SET 
                prelim_grade = ?, midterm_grade = ?, finals_grade = ?, 
                grade = ?, remarks = ? 
                WHERE student_number = ? AND subject_id = ?");
            $update->bind_param("dddsssi", $prelim, $midterm, $finals, $grade, $remarks, $student_number, $subject_id);
            $update->execute();
            
        } else {
            $insert = $conn->prepare("INSERT INTO grades 
                (student_number, subject_id, prelim_grade, midterm_grade, finals_grade, grade, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sidddds", $student_number, $subject_id, $prelim, $midterm, $finals, $grade, $remarks);
            $insert->execute();
        }

        // ✅ Clearance update
        $clr_check = $conn->prepare("SELECT * FROM clearance WHERE student_number = ? AND subject_id = ?");
        $clr_check->bind_param("si", $student_number, $subject_id);
        $clr_check->execute();
        $clr_result = $clr_check->get_result();

        if ($clr_result->num_rows > 0) {
            $clr_update = $conn->prepare("UPDATE clearance SET status = ? WHERE student_number = ? AND subject_id = ?");
            $clr_update->bind_param("ssi", $clearance_status, $student_number, $subject_id);
            $clr_update->execute();
        } else {
            $clr_insert = $conn->prepare("INSERT INTO clearance (student_number, subject_id, status) VALUES (?, ?, ?)");
            $clr_insert->bind_param("sis", $student_number, $subject_id, $clearance_status);
            $clr_insert->execute();
        }

        // 🪵 Log per subject
        logFacultyActivity($conn, $faculty_number, "Updated grades and clearance for student #$student_number - $full_name in subject $subject_name");
    }

    logThis($conn, $faculty_number, "Batch updated subjects for $student_number - $full_name", 'faculty');

    log_activity_action($conn, "Saved grades for student: $student_number");


    header("Location: ../faculty_dashboard.php?page=faculty_manage_grades&saved=1");
    exit();
} else {
    echo "Invalid request.";
}
?>
