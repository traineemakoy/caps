<?php
// ✅ Include DB connection at logging module
include('../includes/db.php');
include('../includes/log_module.php');

// ✅ Kukunin ang registrar ID para sa logging
$registrar_id = $_SESSION['registrar_id'] ?? null;

// ✅ Initialize variables
$subject_code = '';
$subject_name = '';
$course_ids = [];
$edit_id = null;
$error = '';

// ✅ Handle Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    // Kinukuha ang values mula form
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $course_ids = isset($_POST['course_ids']) ? $_POST['course_ids'] : [];

    // Convert array to comma-separated string
    $joined_courses = implode(",", $course_ids);

    // Check kung existing na yung subject_code
    $check = $conn->prepare("SELECT * FROM subjects WHERE subject_code = ?");
    $check->bind_param("s", $subject_code);
    $check->execute();
    $result = $check->get_result();

    // Kapag existing, show error
    if ($result->num_rows > 0) {
        $error = "Subject code already exists.";
    } else {
        // Insert sa database
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, course_ids) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $subject_code, $subject_name, $joined_courses);
        $stmt->execute();

        // Log the action
        logThis($conn, $registrar_id, "Added subject: [$subject_code] $subject_name");

        // Redirect after success
        header("Location: registrar_dashboard.php?page=subjects&status=added");
        exit();
    }
}

// ✅ Handle Edit Load
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];

    // Kunin ang subject data
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    // Set default values for form
    $subject_code = $row['subject_code'];
    $subject_name = $row['subject_name'];
    $course_ids = explode(",", $row['course_ids']);
}

// ✅ Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subject'])) {
    $id = $_POST['subject_id'];
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $course_ids = isset($_POST['course_ids']) ? $_POST['course_ids'] : [];
    $joined_courses = implode(",", $course_ids);

    // Update the subject
    $stmt = $conn->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, course_ids = ? WHERE subject_id = ?");
    $stmt->bind_param("sssi", $subject_code, $subject_name, $joined_courses, $id);
    $stmt->execute();
    log_activity_action($conn, "Updated subject: [$subject_code] $subject_name");


    // Log the update
    

    // Redirect after update
    header("Location: registrar_dashboard.php?page=subjects&status=updated");
    exit();
}

// ✅ Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Kunin muna ang subject name for logging
    $stmt = $conn->prepare("SELECT subject_code, subject_name FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $res = $stmt->get_result()->fetch_assoc();
    $log_text = "Deleted subject: [{$res['subject_code']}] {$res['subject_name']}";

    // Delete sa database
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    log_activity_action($conn, $log_text);


    // Log deletion
    logThis($conn, $registrar_id, $log_text);

    // Redirect after delete
    header("Location: registrar_dashboard.php?page=subjects");
    exit();
}

// ✅ Load all subjects and courses
$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_code ASC");
$courses = $conn->query("SELECT * FROM courses ORDER BY course_name ASC");

// ✅ Helper function para ipakita ang course names
function getCourseNames($ids, $conn) {
    if (empty($ids)) return '';
    $idArray = explode(",", $ids);
    $placeholders = implode(",", array_fill(0, count($idArray), "?"));

    $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id IN ($placeholders)");
    $stmt->bind_param(str_repeat("i", count($idArray)), ...$idArray);
    $stmt->execute();
    $res = $stmt->get_result();

    $names = [];
    while ($row = $res->fetch_assoc()) {
        $names[] = $row['course_name'];
    }
    return implode(", ", $names);
}
?>

<!-- ✅ External CSS link -->
<link rel="stylesheet" href="code_layout.css">

<div class="form-container">
    <h2 class="form-title">Subject Management</h2>

    <!-- ✅ Success alert -->
    <?php if (isset($_GET['status'])): ?>
        <div class="alert <?= $_GET['status'] == 'added' ? 'success' : 'info' ?>">
            <?= $_GET['status'] == 'added' ? 'Subject successfully added!' : 'Subject successfully updated!' ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Error display -->
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- ✅ Subject Form -->
    <form method="POST" action="registrar_dashboard.php?page=subjects">
        <input type="hidden" name="subject_id" value="<?= $edit_id ?>">

        <label>Subject Code:</label>
        <input type="text" name="subject_code" required value="<?= htmlspecialchars($subject_code) ?>"><br>

        <label>Subject Name:</label>
        <input type="text" name="subject_name" required value="<?= htmlspecialchars($subject_name) ?>"><br>

        <label>Courses:</label><br>
        <div class="checkbox-group">
        <?php foreach ($courses as $course): ?>
            <label>
                <input type="checkbox" name="course_ids[]" value="<?= $course['course_id'] ?>"
                    <?= in_array($course['course_id'], $course_ids) ? 'checked' : '' ?>>
                <?= htmlspecialchars($course['course_name']) ?>
            </label>
        <?php endforeach; ?>
        </div><br>

        <!-- ✅ Submit buttons -->
        <?php if ($edit_id): ?>
            <button type="submit" name="update_subject">Update Subject</button>
            <a href="registrar_dashboard.php?page=subjects" class="btn-cancel">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_subject">Add Subject</button>
        <?php endif; ?>
    </form>
</div>

<!-- ✅ Subject Table -->
<div class="table-wrapper">
<table>
    <thead>
        <tr>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Courses</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $subjects->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject_code']) ?></td>
            <td><?= htmlspecialchars($row['subject_name']) ?></td>
            <td><?= htmlspecialchars(getCourseNames($row['course_ids'], $conn)) ?></td>
            <td>
                <a href="registrar_dashboard.php?page=subjects&edit=<?= $row['subject_id'] ?>" onclick="return confirm('Edit this subject?')">Edit</a> |
                <a href="registrar_dashboard.php?page=subjects&delete=<?= $row['subject_id'] ?>" onclick="return confirm('Are you sure you want to delete this subject?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
