<?php
include("../includes/db.php");
session_start();

$student_number = $_SESSION['student_number'] ?? null;
if (!$student_number) {
    header("Location: ../index.php");
    exit();
}

$stmt = $conn->prepare("SELECT first_name, last_name FROM students WHERE student_number = ?");
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$full_name = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<!-- 🔝 Topbar -->
<div class="topbar">
    <div>Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></div>
    <form method="POST" action="../logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<!-- 📚 Sidebar -->
<div class="sidebar">
    <button onclick="location.href='?page=student_profile'">Student</button>
    <button onclick="location.href='?page=enrolled_subjects'">Subject Enrollment</button>
    <button onclick="location.href='?page=view_enrolled_subjects'">Subject Enrollment History</button>
    <button onclick="location.href='?page=view_grades'">View Grades</button>
    <button onclick="location.href='?page=clearance_status'">Clearance</button>
    <button onclick="location.href='?page=student_at_risk'">At Risk Subjects</button>
    <button onclick="location.href='?page=student_announcements'">Announcement</button>
    <button onclick="location.href='?page=student_dashboard_presence'">Announcement</button>

</div>

<!-- 🧱 Content Area -->
<div class="content">
<?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        $allowed = [
            'student_profile',
            'student_profile',
            'enrolled_subjects',
            'view_enrolled_subjects',
            'view_grades',
            'clearance_status',
            'student_at_risk',
            'student_announcements',
            'student_dashboard_presence'
        ];
        if (in_array($page, $allowed)) {
            include("pages/$page.php");
        } else {
            echo "<h3>⚠️ Page not found.</h3>";
        }
    } else {
        echo "<h3>📌 Welcome to your Student Dashboard!</h3>";
    }
?>
</div>

</body>
</html>
