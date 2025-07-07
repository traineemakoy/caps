<?php
// 👉 Connect sa database at include log module
include('../includes/db.php');
include('../includes/log_module.php');

// 👉 Start session
session_start();

// 👉 Kunin ang registrar ID kung naka-login
$registrar_id = $_SESSION['registrar_id'] ?? null;

// 👉 I-check kung may student_number sa URL
if (!isset($_GET['student_number'])) {
    die("Student number is required.");
}

// 👉 Kunin ang student_number
$student_number = $_GET['student_number'];

// 👉 Query para kunin ang grades at subject info
$query = $conn->prepare("SELECT g.grade, g.remarks, s.subject_code, s.subject_name
                         FROM grades g
                         JOIN subjects s ON g.subject_id = s.subject_id
                         WHERE g.student_number = ?");
$query->bind_param("s", $student_number);
$query->execute();
$result = $query->get_result();

// 👉 Query para kunin ang student info (name + course)
$student_info = $conn->prepare("SELECT first_name, last_name, middle_name, course 
                                FROM students 
                                WHERE student_number = ?");
$student_info->bind_param("s", $student_number);
$student_info->execute();
$student = $student_info->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grades</title>
    <!-- ✅ Uniform external CSS -->
    <link rel="stylesheet" href="code_layout.css">
</head>
<body>

    <!-- 👉 Main heading -->
    <h2>🎓 Grades</h2>

    <!-- 👉 Student Info Section -->
    <div style="margin-bottom: 20px;">
        <p><strong>Student Name:</strong> <?= htmlspecialchars("{$student['last_name']}, {$student['first_name']} {$student['middle_name']}") ?></p>
        <p><strong>Student Number:</strong> <?= htmlspecialchars($student_number) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
    </div>

    <!-- 👉 Grades Table -->
    <table class="clearance-table">
        <tr>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Grade</th>
            <th>Remarks</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_code']) ?></td>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= htmlspecialchars($row['grade']) ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

</body>
</html>
