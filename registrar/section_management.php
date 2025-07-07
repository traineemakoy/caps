<?php
include('../includes/db.php'); // 👉 Include DB connection
include('../includes/log_module.php'); // 👉 Include logging module


$registrar_id = $_SESSION['registrar_id'] ?? null;

// ✅ ADD section
if (isset($_POST['add'])) {
    $section_name = $_POST['section_name'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $term = $_POST['term'];

    $stmt = $conn->prepare("INSERT INTO sections (section_name, course, year_level, term) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $section_name, $course, $year_level, $term);
    $stmt->execute();

    // Logging
    log_activity_action($conn, "Added Section: $section_name");
    logThis($conn, $registrar_id, "📘 Added section: $section_name — $course / $year_level / $term");

    header("Location: registrar_dashboard.php?page=section_management&status=added");
    exit();
}

// ✅ UPDATE section
if (isset($_POST['update'])) {
    $id = $_POST['section_id'];
    $section_name = $_POST['section_name'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $term = $_POST['term'];

    $stmt = $conn->prepare("UPDATE sections SET section_name=?, course=?, year_level=?, term=? WHERE section_id=?");
    $stmt->bind_param("ssssi", $section_name, $course, $year_level, $term, $id);
    $stmt->execute();

    log_activity_action($conn, "Updated Section: $section_name");
    logThis($conn, $registrar_id, "✏️ Updated section: $section_name — $course / $year_level / $term");

    echo "<script>alert('✏️ Section updated successfully!'); window.location.href='registrar_dashboard.php?page=section_management';</script>";
    exit();
}

// ✅ DELETE section
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Fetch data for logging
    $res = $conn->query("SELECT * FROM sections WHERE section_id = $id");
    $section = $res->fetch_assoc();
    $section_name = $section['section_name'] ?? 'Unknown';
    $course = $section['course'] ?? '';
    $year_level = $section['year_level'] ?? '';
    $term = $section['term'] ?? '';

    // Log before delete
    log_activity_action($conn, "Deleted Section: $section_name");
    logThis($conn, $registrar_id, "🗑️ Deleted section: $section_name — $course / $year_level / $term");

    $conn->query("DELETE FROM sections WHERE section_id=$id");

    echo "<script>alert('🗑️ Section deleted successfully!'); window.location.href='registrar_dashboard.php?page=section_management';</script>";
    exit();
}


// ✅ EDIT mode check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM sections WHERE section_id=$id")->fetch_assoc();
}

// ✅ Fetch dropdown options
$courses = $conn->query("SELECT * FROM courses");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_id DESC");
?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
    <div class="alert success">✅ Section successfully added!</div>
<?php endif; ?>

<h2>📘 Section Management</h2>

<form method="POST" class="section-form">
    <input type="hidden" name="section_id" value="<?= $edit_mode ? $edit_data['section_id'] : '' ?>">

    <input type="text" name="section_name" placeholder="Section Name" required value="<?= $edit_mode ? $edit_data['section_name'] : '' ?>">

    <select name="course" required>
        <option value="">Select Course</option>
        <?php while ($c = $courses->fetch_assoc()): ?>
            <option value="<?= $c['course_name'] ?>" <?= ($edit_mode && $edit_data['course'] == $c['course_name']) ? 'selected' : '' ?>>
                <?= $c['course_name'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <select name="year_level" required>
        <option value="">Year Level</option>
        <?php
        $years = ['1st Year', '2nd Year', '3rd Year'];
        foreach ($years as $year) {
            $selected = ($edit_mode && $edit_data['year_level'] == $year) ? 'selected' : '';
            echo "<option value='$year' $selected>$year</option>";
        }
        ?>
    </select>

    <select name="term" required>
        <option value="">Term</option>
        <?php
        $terms = ['1st Term', '2nd Term', '3rd Term'];
        foreach ($terms as $term) {
            $selected = ($edit_mode && $edit_data['term'] == $term) ? 'selected' : '';
            echo "<option value='$term' $selected>$term</option>";
        }
        ?>
    </select>

    <?php if ($edit_mode): ?>
        <button type="submit" name="update">Update Section</button>
        <a href="registrar_dashboard.php?page=section_management">Cancel</a>
    <?php else: ?>
        <button type="submit" name="add">Add Section</button>
    <?php endif; ?>
</form>

<table class="section-table">
    <tr>
        <th>ID</th>
        <th>Section</th>
        <th>Course</th>
        <th>Year Level</th>
        <th>Term</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $sections->fetch_assoc()): ?>
    <tr>
        <td><?= $row['section_id'] ?></td>
        <td><?= $row['section_name'] ?></td>
        <td><?= $row['course'] ?></td>
        <td><?= $row['year_level'] ?></td>
        <td><?= $row['term'] ?></td>
        <td>
            <a href="registrar_dashboard.php?page=section_management&edit=<?= $row['section_id'] ?>" onclick="return confirm('Are you sure you want to edit this section?')">🖑 Edit</a>
            <a href="registrar_dashboard.php?page=section_management&delete=<?= $row['section_id'] ?>" onclick="return confirm('Are you sure you want to delete this section?')">🖑 Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<link rel="stylesheet" href="code_layout.css">
