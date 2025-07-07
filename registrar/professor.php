<?php
// ✅ Include database connection at logging module
include('../includes/db.php');
include('../includes/log_module.php');

if (session_status() === PHP_SESSION_NONE) session_start();
$registrar_id = $_SESSION['registrar_id'] ?? null;

// ✅ Handle Add Professor
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $faculty_number = $_POST['faculty_number'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $courses = isset($_POST['courses_handled']) ? implode(", ", $_POST['courses_handled']) : '';

    $stmt = $conn->prepare("INSERT INTO faculty (faculty_number, username, password, full_name, courses_handled) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $faculty_number, $username, $password, $full_name, $courses);
    $stmt->execute();

    // ✅ Log for adding professor
    log_activity_action($conn, "Added Professor: $full_name ($faculty_number)");
    logThis($conn, $registrar_id, "Added professor: $full_name with courses [$courses]");

    header("Location: registrar_dashboard.php?page=professor");
    exit();
}

// ✅ Handle Update Professor
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $faculty_number = $_POST['faculty_number'];
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $courses = isset($_POST['courses_handled']) ? implode(", ", $_POST['courses_handled']) : '';

    $stmt = $conn->prepare("UPDATE faculty SET username=?, full_name=?, courses_handled=? WHERE faculty_number=?");
    $stmt->bind_param("ssss", $username, $full_name, $courses, $faculty_number);
    $stmt->execute();

    // ✅ Log for updating
    log_activity_action($conn, "Updated Professor: $full_name ($faculty_number)");
    logThis($conn, $registrar_id, "Updated professor: $full_name — New Courses: [$courses]");

    header("Location: registrar_dashboard.php?page=professor");
    exit();
}

// ✅ Handle Delete Professor
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $get = $conn->prepare("SELECT full_name FROM faculty WHERE faculty_number=?");
    $get->bind_param("s", $id);
    $get->execute();
    $res = $get->get_result();
    $name = $res->fetch_assoc()['full_name'] ?? 'Unknown';

    $stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_number=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();

    // ✅ Log for deleting
    log_activity_action($conn, "Deleted Professor: $name ($id)");
    logThis($conn, $registrar_id, "Deleted professor: $name (Faculty #: $id)");

    header("Location: registrar_dashboard.php?page=professor");
    exit();
}

// ✅ If Edit Mode
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_number=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();

    // ✅ Log view/edit
    log_activity_action($conn, "Viewing/Edit Professor: {$edit_data['full_name']} ($id)");
}

// ✅ Load list
$professors = $conn->query("SELECT * FROM faculty");
?>


<!-- ✅ External CSS -->
<link rel="stylesheet" href="code_layout.css">

<!-- ✅ Form container para sa Add/Edit -->
<div class="form-container">
    <h2 class="form-title"><?= $edit_mode ? 'Edit' : 'Add' ?> Professor</h2>
    <form method="POST" action="registrar_dashboard.php?page=professor">
        <!-- Hidden input to identify action -->
        <input type="hidden" name="action" value="<?= $edit_mode ? 'update' : 'add' ?>">

        <!-- Faculty Number -->
        <label>Faculty Number:</label>
        <input type="text" name="faculty_number" required value="<?= $edit_mode ? $edit_data['faculty_number'] : '' ?>" <?= $edit_mode ? 'readonly' : '' ?>>

        <!-- Username -->
        <label>Username:</label>
        <input type="text" name="username" required value="<?= $edit_mode ? $edit_data['username'] : '' ?>">

        <!-- Password (only shown if adding new) -->
        <?php if (!$edit_mode): ?>
            <label>Password:</label>
            <input type="password" name="password" required>
        <?php endif; ?>

        <!-- Full Name -->
        <label>Full Name:</label>
        <input type="text" name="full_name" required value="<?= $edit_mode ? $edit_data['full_name'] : '' ?>">

        <!-- Courses Handled (checkboxes) -->
        <label>Courses Handled:</label>
        <div class="checkbox-group">
            <?php
            $selected_courses = $edit_mode ? explode(", ", $edit_data['courses_handled']) : [];
            $course_q = $conn->query("SELECT course_name FROM courses");
            while ($row = $course_q->fetch_assoc()) {
                $course = $row['course_name'];
                $checked = in_array($course, $selected_courses) ? 'checked' : '';
                echo "<label><input type='checkbox' name='courses_handled[]' value='$course' $checked> $course</label>";
            }
            ?>
        </div>

        <!-- Submit Button -->
        <button type="submit"><?= $edit_mode ? 'Update' : 'Add' ?> Professor</button>
    </form>
</div>

<!-- ✅ Table para sa professor list -->
<h2 class="section-title">Professor List</h2>
<div class="table-wrapper">
<table>
    <tr>
        <th>Faculty #</th>
        <th>Username</th>
        <th>Full Name</th>
        <th>Courses Handled</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $professors->fetch_assoc()): ?>
        <tr>
            <td><?= $row['faculty_number'] ?></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['full_name'] ?></td>
            <td><?= $row['courses_handled'] ?></td>
            <td>
                <a href="registrar_dashboard.php?page=professor&edit=<?= $row['faculty_number'] ?>" onclick="return confirm('Edit this professor?')">Edit</a> |
                <a href="registrar_dashboard.php?page=professor&delete=<?= $row['faculty_number'] ?>" onclick="return confirm('Delete this professor?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</div>
