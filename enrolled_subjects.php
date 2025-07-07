<?php
include("../includes/db.php");


$student_number = $_SESSION['student_number'] ?? null;
if (!$student_number) die("Unauthorized access.");

// 🔁 Handle ENROLL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'enroll') {
    $subject_id = $_POST['subject_id'];
    $term = $_POST['term'];
    $school_year = $_POST['school_year'];

    $check = $conn->prepare("SELECT * FROM enrolled_subjects WHERE student_number = ? AND subject_id = ? AND term = ? AND school_year = ?");
    $check->bind_param("siss", $student_number, $subject_id, $term, $school_year);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO enrolled_subjects (student_number, subject_id, term, school_year) VALUES (?, ?, ?, ?)");
        $insert->bind_param("siss", $student_number, $subject_id, $term, $school_year);
        $insert->execute();
    }

    header("Location: student_dashboard.php?page=enrolled_subjects");
    exit();
}

// 🔁 Handle UNENROLL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'unenroll') {
    $subject_id = $_POST['subject_id'];
    $term = $_POST['term'];
    $school_year = $_POST['school_year'];

    $delete = $conn->prepare("DELETE FROM enrolled_subjects WHERE student_number = ? AND subject_id = ? AND term = ? AND school_year = ?");
    $delete->bind_param("siss", $student_number, $subject_id, $term, $school_year);
    $delete->execute();

    header("Location: student_dashboard.php?page=enrolled_subjects");
    exit();
}

// 🧠 Get student info
$student_q = $conn->prepare("SELECT course, year_level FROM students WHERE student_number = ?");
$student_q->bind_param("s", $student_number);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();
$course = $student['course'];
$year_level = $student['year_level'];

// 📚 Get all offered subjects for student’s course and year
$stmt = $conn->prepare("SELECT os.*, s.subject_code, s.subject_name, f.full_name 
                        FROM offered_subjects os
                        JOIN subjects s ON os.subject_id = s.subject_id
                        JOIN faculty f ON os.faculty_number = f.faculty_number
                        ORDER BY os.year_level, os.term, os.school_year");
$stmt->execute();
$subjects = $stmt->get_result();

// 🎯 Get all enrolled subjects of student
$enrolled = $conn->prepare("SELECT subject_id FROM enrolled_subjects WHERE student_number = ?");
$enrolled->bind_param("s", $student_number);
$enrolled->execute();
$enrolled_result = $enrolled->get_result();

$enrolled_ids = [];
while ($row = $enrolled_result->fetch_assoc()) {
    $enrolled_ids[] = $row['subject_id'];
}
?>

<h3>📘 Subject Enrollment</h3>

<?php
$current_group = "";
while ($row = $subjects->fetch_assoc()) {
    // Skip if not intended for this student’s course (but allow general subjects)
    if (strpos($row['course'], $course) === false && strpos($row['course'], "All Courses") === false) {
        continue;
    }

    $group = $row['year_level'] . " – " . $row['term'] . " – " . $row['school_year'];

    // New group = open table
    if ($group !== $current_group) {
        if ($current_group !== "") echo "</table><br>";
        echo "<h4>📗 $group</h4>";
        echo "<table border='1' cellpadding='10' cellspacing='0' width='100%'>
                <tr style='background-color:green; color:white;'>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Professor</th>
                    <th>Term</th>
                    <th>School Year</th>
                    <th>Action</th>
                </tr>";
        $current_group = $group;
    }

    // Print each row
    echo "<tr>
            <td>" . htmlspecialchars($row['subject_code']) . "</td>
            <td>" . htmlspecialchars($row['subject_name']) . "</td>
            <td>" . htmlspecialchars($row['full_name']) . "</td>
            <td>" . htmlspecialchars($row['term']) . "</td>
            <td>" . htmlspecialchars($row['school_year']) . "</td>
            <td>
                <form method='POST' onsubmit='return confirm(\"Are you sure?\");'>
                    <input type='hidden' name='subject_id' value='" . $row['subject_id'] . "'>
                    <input type='hidden' name='term' value='" . $row['term'] . "'>
                    <input type='hidden' name='school_year' value='" . $row['school_year'] . "'>";

    if (in_array($row['subject_id'], $enrolled_ids)) {
        echo "<input type='hidden' name='action' value='unenroll'>
              <button type='submit' style='background-color:red; color:white;'>Unenroll</button>";
    } else {
        echo "<input type='hidden' name='action' value='enroll'>
              <button type='submit'>Enroll</button>";
    }

    echo "</form>
          </td>
        </tr>";
}
if ($current_group !== "") echo "</table>"; // close last table
?>

<link rel="stylesheet" href="../assets/css/dashboard.css">
