<?php
include("../includes/db.php");
include('../includes/log_module.php');

$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) {
    header("Location: ../index.php");
    exit();
}

// 🔽 Load filter options
$courses = $conn->query("SELECT DISTINCT course FROM students ORDER BY course");
$years = $conn->query("SELECT DISTINCT year_level FROM students ORDER BY year_level");
$sections = $conn->query("SELECT DISTINCT section FROM students ORDER BY section");

// 📌 Get filter selections
$filter_course = $_POST['course'] ?? '';
$filter_year = $_POST['year_level'] ?? '';
$filter_section = $_POST['section'] ?? '';
$active_student = $_POST['active_student'] ?? null;
$students = [];

// 🔍 Query students matching filters
if ($filter_course && $filter_year && $filter_section) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE course = ? AND year_level = ? AND section = ?");
    $stmt->bind_param("sss", $filter_course, $filter_year, $filter_section);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<h2>📚 Manage Grades</h2>

<!-- 🔘 Filter Form -->
<form method="POST">
    <label>Course:</label>
    <select name="course" required onchange="this.form.submit()">
        <option value="">-- Select Course --</option>
        <?php while ($row = $courses->fetch_assoc()): ?>
            <option value="<?= $row['course'] ?>" <?= $filter_course == $row['course'] ? 'selected' : '' ?>><?= $row['course'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Year Level:</label>
    <select name="year_level" required onchange="this.form.submit()">
        <option value="">-- Select Year --</option>
        <?php while ($row = $years->fetch_assoc()): ?>
            <option value="<?= $row['year_level'] ?>" <?= $filter_year == $row['year_level'] ? 'selected' : '' ?>><?= $row['year_level'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Section:</label>
    <select name="section" required onchange="this.form.submit()">
        <option value="">-- Select Section --</option>
        <?php while ($row = $sections->fetch_assoc()): ?>
            <option value="<?= $row['section'] ?>" <?= $filter_section == $row['section'] ? 'selected' : '' ?>><?= $row['section'] ?></option>
        <?php endwhile; ?>
    </select>
</form>

<?php if ($students && $students->num_rows > 0): ?>
    <table border="1" cellpadding="10">
        <tr>
            <th>Student No.</th>
            <th>Full Name</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $students->fetch_assoc()): ?>
            <tr>
                <td><?= $row['student_number'] ?></td>
                <td><?= $row['last_name'] ?>, <?= $row['first_name'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="course" value="<?= $filter_course ?>">
                        <input type="hidden" name="year_level" value="<?= $filter_year ?>">
                        <input type="hidden" name="section" value="<?= $filter_section ?>">
                        <input type="hidden" name="active_student" value="<?= $row['student_number'] ?>">
                        <button type="submit">Manage Grades</button>
                    </form>
                </td>
            </tr>

            <?php if ($active_student == $row['student_number']): ?>
                <tr>
                    <td colspan="3">
                        <form method="POST" action="pages/save_grades.php">
                            <input type="hidden" name="student_number" value="<?= $row['student_number'] ?>">

                            <table border="1" cellpadding="5">
                                <tr>
                                    <th>Subject</th>
                                    <th>Prelim</th>
                                    <th>Midterm</th>
                                    <th>Finals</th>
                                    <th>Official Grade</th>
                                    <th>Remarks</th>
                                    <th>Clearance</th>
                                </tr>

                                <?php
                                $student_number = $row['student_number'];

                                $stmt = $conn->prepare("
                                    SELECT s.subject_id, s.subject_code, s.subject_name
                                    FROM enrolled_subjects es
                                    JOIN offered_subjects os ON es.subject_id = os.subject_id
                                    JOIN subjects s ON s.subject_id = os.subject_id
                                    WHERE os.faculty_number = ? AND es.student_number = ?
                                ");
                                $stmt->bind_param("ss", $faculty_number, $student_number);
                                $stmt->execute();
                                $subjects = $stmt->get_result();

                                while ($subject = $subjects->fetch_assoc()):
                                    $subject_id = $subject['subject_id'];

                                    $grade_q = $conn->prepare("SELECT * FROM grades WHERE student_number = ? AND subject_id = ?");
                                    $grade_q->bind_param("si", $student_number, $subject_id);
                                    $grade_q->execute();
                                    $grade_data = $grade_q->get_result()->fetch_assoc();

                                    $clr_q = $conn->prepare("SELECT status FROM clearance WHERE student_number = ? AND subject_id = ?");
                                    $clr_q->bind_param("si", $student_number, $subject_id);
                                    $clr_q->execute();
                                    $clr_data = $clr_q->get_result()->fetch_assoc();
                                ?>
                                    <tr>
                                        <td><?= $subject['subject_code'] ?> - <?= $subject['subject_name'] ?></td>
                                        
                                        <!-- Prelim -->
                                        <td>
                                            <input type="number" step="0.1" min="1" max="5"
                                                name="grades[<?= $subject_id ?>][prelim]"
                                                value="<?= $grade_data['prelim_grade'] ?? '' ?>">
                                        </td>

                                        <!-- Midterm -->
                                        <td>
                                            <input type="number" step="0.1" min="1" max="5"
                                                name="grades[<?= $subject_id ?>][midterm]"
                                                value="<?= $grade_data['midterm_grade'] ?? '' ?>">
                                        </td>

                                        <!-- Finals -->
                                        <td>
                                            <input type="number" step="0.1" min="1" max="5"
                                                name="grades[<?= $subject_id ?>][finals]"
                                                value="<?= $grade_data['finals_grade'] ?? '' ?>">
                                        </td>

                                        <!-- Final Grade (readonly) -->
                                        <td>
                                            <input type="number" step="0.01" name="final_computed"
                                                value="<?= $grade_data['grade'] ?? '' ?>" readonly>
                                        </td>

                                        <!-- Remarks -->
                                        <td>
                                            <input type="text" name="grades[<?= $subject_id ?>][remarks]"
                                                value="<?= $grade_data['remarks'] ?? '' ?>" readonly>
                                        </td>

                                        <!-- Clearance -->
                                        <td>
                                            <select name="grades[<?= $subject_id ?>][clearance]">
                                                <option <?= ($clr_data['status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option <?= ($clr_data['status'] ?? '') == 'Cleared' ? 'selected' : '' ?>>Cleared</option>
                                                <option <?= ($clr_data['status'] ?? '') == 'Not Cleared' ? 'selected' : '' ?>>Not Cleared</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </table>

                            <br><button type="submit">💾 Save Grades</button>
                        </form>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

<?php if (isset($_GET['saved'])): ?>
    <p style="color: green;">✅ Grades saved successfully!</p>
<?php endif; ?>

<link rel="stylesheet" href="../../assets/css/dashboard.css">
