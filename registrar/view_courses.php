<?php
include('../includes/db.php'); // 👉 Kinukuha yung database connection
include('../includes/log_module.php'); // 👉 Para sa activity logging
$registrar_id = $_SESSION['registrar_id'] ?? null; // 👉 Kinukuha ang registrar ID from session kung naka-login

// 👉 Initial default values
$course_code = '';
$course_name = '';
$button_label = 'Add Course';
$edit_mode = false;

// ✅ Handle Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $code = $_POST['course_code']; // 👉 Kinukuha ang code from form input
    $name = $_POST['course_name']; // 👉 Kinukuha ang name from form input

    // 👉 Check kung may existing na course_code
    $check = $conn->prepare("SELECT * FROM courses WHERE course_code = ?");
    $check->bind_param("s", $code);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // 👉 May duplicate, alert tapos redirect
        echo "<script>
            alert('⚠️ Duplicate course code detected!');
            window.location.href='?page=view_courses';
        </script>";
        exit;
    }

    // 👉 Insert kung walang duplicate
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $code, $name);

    if ($stmt->execute()) {
        header("Location: ?page=view_courses&status=success");
        exit;
    } else {
        header("Location: ?page=view_courses&status=error");
        exit;
    }
}

// ✅ Handle Update Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $id = $_POST['course_id']; // 👉 ID ng course na ie-edit
    $code = $_POST['course_code'];
    $name = $_POST['course_name'];

    // 👉 Update query
    $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ? WHERE course_id = ?");
    $stmt->bind_param("ssi", $code, $name, $id);
    $stmt->execute();

    // 👉 Logging for update
    logThis($conn, $registrar_id, "📘 Updated course: [$code] $name (ID: $id)");

    // 👉 Show success message then redirect
    echo "<script>alert('✅ Course updated successfully!'); window.location.href='?page=view_courses';</script>";
    exit;
}

// ✅ Handle Delete Course
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete']; // 👉 ID ng course na ide-delete

    // 👉 Kunin muna course info for logging
    $res = $conn->query("SELECT * FROM courses WHERE course_id = $delete_id");
    $course = $res->fetch_assoc();
    $code = $course['course_code'] ?? 'N/A';
    $name = $course['course_name'] ?? 'Unknown';

    // 👉 Delete query
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    logThis($conn, $registrar_id, "🗑️ Deleted course: [$code] $name (ID: $delete_id)");
    echo "<script>alert('🗑️ Course deleted successfully!'); window.location.href='?page=view_courses';</script>";
    exit;
}

// ✅ Handle Edit Mode
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];

    // 👉 Fetch course info to prefill the form
    $res = $conn->query("SELECT * FROM courses WHERE course_id = $edit_id");
    $row = $res->fetch_assoc();
    $course_code = $row['course_code'];
    $course_name = $row['course_name'];
    $button_label = 'Update Course';
}
?>

<!-- ✅ Alert messages after add/update -->
<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="alert success">✅ Course successfully added!</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
    <div class="alert error">❌ Something went wrong. Please try again.</div>
<?php endif; ?>

<!-- ✅ Main Title -->
<h2>📘 Course Management</h2>

<!-- ✅ Add or Edit Course Form -->
<form method="POST" class="course-form">
    <input type="hidden" name="course_id" value="<?= $_GET['edit'] ?? '' ?>"> <!-- Hidden ID for editing -->
    <input type="text" name="course_code" placeholder="Course Code" value="<?= $course_code ?>" required>
    <input type="text" name="course_name" placeholder="Course Name" value="<?= $course_name ?>" required>
    <button type="submit" name="<?= $edit_mode ? 'update_course' : 'add_course' ?>">
        <?= $button_label ?>
    </button>
    <?php if ($edit_mode): ?>
        <a href="?page=view_courses" class="cancel-link">Cancel</a>
    <?php endif; ?>
</form>

<!-- ✅ Table of All Courses -->
<table class="course-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM courses ORDER BY course_id ASC");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['course_id'] ?></td>
            <td><?= htmlspecialchars($row['course_code']) ?></td>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td>
                🖉 <a href="?page=view_courses&edit=<?= $row['course_id'] ?>">Edit</a> |
                🗑️ <a href="?page=view_courses&delete=<?= $row['course_id'] ?>" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- ✅ Link to external CSS -->
<link rel="stylesheet" href="code_layout.css">
