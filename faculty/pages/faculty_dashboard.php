<?php
include("../includes/db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) {
    header("Location: ../index.php");
    exit();
}

// Get full name
$stmt = $conn->prepare("SELECT full_name FROM faculty WHERE faculty_number = ?");
$stmt->bind_param("s", $faculty_number);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();
$faculty_name = $faculty['full_name'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<!-- 🔝 Topbar -->
<div class="topbar">
    <div>Welcome, <strong><?= htmlspecialchars($faculty_name) ?></strong></div>
    <form method="POST" action="../logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<!-- 📚 Sidebar -->
<!-- 📚 Sidebar -->
<div class="sidebar">
    <button onclick="location.href='?page=faculty_manage_grades'">Manage Grades</button>
    <button onclick="location.href='?page=announcement'">Announcements</button>
    <button onclick="location.href='?page=my_subjects'">My Subjects</button>
    <button onclick="location.href='?page=login_fingerprint'">Fingerprint</button>
    <button onclick="location.href='?page=faculty_at_risk'">At Risk Students</button>
    <button onclick="location.href='?page=log_attendance'">Fingerprint</button>
</div>


<!-- 🧱 Content Area -->
<div class="content">
<?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        $allowed_pages = [
            'faculty_manage_grades',
            'announcement',
            'faculty_activity_log',
            'my_subjects',
            'login_fingerprint',
            'faculty_at_risk',
            'log_attendance',
        ];
        if (in_array($page, $allowed_pages)) {
            include("pages/$page.php");
        } else {
            echo "<h3>⚠️ Page not found.</h3>";
        }
    } else {
        echo "<h3>📌 Welcome to your Faculty Dashboard!</h3>";
    }
?>
</div>

</body>
</html>
