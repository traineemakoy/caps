<?php
// ☑️ Start ng session para makuha ang student info

// ☑️ I-include ang database connection
include('../includes/db.php');

// ☑️ Kukunin natin ang student_number sa session
$student_number = $_SESSION['student_number'] ?? null;

// ☑️ Redirect kapag walang session (di nakalogin)
if (!$student_number) {
    header("Location: ../login.php");
    exit();
}

// ☑️ Query para kunin ang info ng currently logged-in student
$query = $conn->prepare("SELECT * FROM students WHERE student_number = ?");
$query->bind_param("s", $student_number);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();
?>

<h2>📋 Student Profile</h2>

<?php if ($student): ?>
    <table border="1" cellpadding="10" style="border-collapse: collapse;">
        <tr><th>Student Number</th><td><?= htmlspecialchars($student['student_number']) ?></td></tr>
        <tr><th>Name</th><td><?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?></td></tr>
        <tr><th>Course</th><td><?= htmlspecialchars($student['course']) ?></td></tr>
        <tr><th>Year Level</th><td><?= htmlspecialchars($student['year_level']) ?></td></tr>
        <tr><th>Section</th><td><?= htmlspecialchars($student['section']) ?></td></tr>
        <tr><th>Term</th><td><?= htmlspecialchars($student['term']) ?></td></tr>
        <tr><th>Transferee</th><td><?= htmlspecialchars($student['transferee']) ?></td></tr>
        <tr><th>Irregular</th><td><?= htmlspecialchars($student['irregular']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($student['email']) ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($student['phone_number']) ?></td></tr>
    </table>
<?php else: ?>
    <p>No student profile found.</p>
<?php endif; ?>


<link rel="stylesheet" href="../assets/css/dashboard.css">