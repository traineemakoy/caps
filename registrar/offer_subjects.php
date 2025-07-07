<?php
include('../includes/db.php');
include('../includes/log_module.php');
$registrar_id = $_SESSION['registrar_id'] ?? null;

// ✅ Handle deletion request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del = $conn->prepare("DELETE FROM offered_subjects WHERE id = ?");
    $del->bind_param("i", $delete_id);
    $del->execute();
    echo "<script>alert('🗑️ Offered subject deleted.'); window.location.href='?page=offer_subjects';</script>";
    exit();
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_subject'])) {
    $subject_id = $_POST['subject_id'];
    $faculty_number = $_POST['faculty_number'];
    $course = $_POST['course_display'];
    $year_level = $_POST['year_level'];
    $term = $_POST['term'];
    $school_year = $_POST['school_year'];

    $check = $conn->prepare("SELECT * FROM offered_subjects WHERE subject_id=? AND faculty_number=? AND course=? AND year_level=? AND term=? AND school_year=?");
    $check->bind_param("isssss", $subject_id, $faculty_number, $course, $year_level, $term, $school_year);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        echo "<script>alert('⚠️ Already offered!');</script>";
    } else {
        $insert = $conn->prepare("INSERT INTO offered_subjects (subject_id, faculty_number, course, year_level, term, school_year) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isssss", $subject_id, $faculty_number, $course, $year_level, $term, $school_year);
        $insert->execute();
        echo "<script>alert('✅ Subject offered!'); window.location.href='?page=offer_subjects';</script>";
        log_activity_action($conn, "Offered subject ID: $subject_id to faculty: $faculty_number for course: $course ($year_level, $term $school_year)");
        exit();
    }
}

// ✅ Load dropdowns
$subjects = $conn->query("SELECT * FROM subjects");
$faculty = $conn->query("SELECT * FROM faculty");

// ✅ Auto-fill course names if subject is selected
$selected_subject_id = $_GET['subject_id'] ?? '';
$course_names_display = '';

if ($selected_subject_id) {
    $get_course = $conn->prepare("SELECT course_ids FROM subjects WHERE subject_id=?");
    $get_course->bind_param("i", $selected_subject_id);
    $get_course->execute();
    $result = $get_course->get_result();
    if ($row = $result->fetch_assoc()) {
        $course_ids = explode(",", $row['course_ids']);
        $names = [];

        foreach ($course_ids as $cid) {
            $cid = trim($cid);
            $course_q = $conn->prepare("SELECT course_name FROM courses WHERE course_id=?");
            $course_q->bind_param("i", $cid);
            $course_q->execute();
            $course_res = $course_q->get_result();
            if ($course_row = $course_res->fetch_assoc()) {
                $names[] = $course_row['course_name'];
            }
        }
        $course_names_display = implode(", ", $names);
    }
}
?>

<h2>Offer a Subject</h2>

<!-- 🔁 Subject dropdown (auto-submit) -->
<form method="GET" action="registrar_dashboard.php">
    <input type="hidden" name="page" value="offer_subjects">
    <label>Subject:</label>
    <select name="subject_id" onchange="this.form.submit()" required>
        <option value="">-- Select Subject --</option>
        <?php
        $subjects->data_seek(0);
        while ($row = $subjects->fetch_assoc()) {
            $selected = ($row['subject_id'] == $selected_subject_id) ? 'selected' : '';
            echo "<option value='{$row['subject_id']}' $selected>{$row['subject_code']} - {$row['subject_name']}</option>";
        }
        ?>
    </select>
</form>

<!-- 📋 Main Form -->
<form method="POST">
    <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selected_subject_id) ?>">

    <label>Faculty:</label>
    <select name="faculty_number" required>
        <option value="">-- Select Professor --</option>
        <?php
        $faculty->data_seek(0);
        while ($row = $faculty->fetch_assoc()) {
            echo "<option value='{$row['faculty_number']}'>{$row['full_name']}</option>";
        }
        ?>
    </select><br><br>

    <label>Course:</label>
    <input type="text" name="course_display" value="<?= htmlspecialchars($course_names_display) ?>" placeholder="Type or select a subject first"><br><br>

    <label>Year Level:</label>
    <select name="year_level" required>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
    </select><br><br>

    <label>Term:</label>
    <select name="term" required>
        <option value="1st Term">1st Term</option>
        <option value="2nd Term">2nd Term</option>
        <option value="3rd Term">3rd Term</option>
    </select><br><br>

    <label>School Year:</label>
    <input type="text" name="school_year" placeholder="e.g. 2024-2025" required><br><br>

    <button type="submit" name="offer_subject">Offer Subject</button>
</form>

<hr>

<!-- 📄 Offered Subjects Table -->
<h3>📋 Opened Subjects</h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Professor</th>
        <th>Course</th>
        <th>Year Level</th>
        <th>Term</th>
        <th>School Year</th>
        <th>Action</th>
    </tr>
    <?php
    $list = $conn->query("SELECT os.*, s.subject_code, s.subject_name, f.full_name 
                          FROM offered_subjects os 
                          JOIN subjects s ON os.subject_id = s.subject_id 
                          JOIN faculty f ON os.faculty_number = f.faculty_number");
    while ($row = $list->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['subject_code']} - {$row['subject_name']}</td>
            <td>{$row['full_name']}</td>
            <td>{$row['course']}</td>
            <td>{$row['year_level']}</td>
            <td>{$row['term']}</td>
            <td>{$row['school_year']}</td>
            <td>
                <a href='registrar_dashboard.php?page=offer_subjects&delete_id={$row['id']}' onclick=\"return confirm('Are you sure you want to delete this offered subject?')\">🗑️ Delete</a>
            </td>
        </tr>";
    }
    ?>
</table>

<link rel="stylesheet" href="code_layout.css">
