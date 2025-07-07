<?php
// 👉 Kinokonek ang database at log system
include('../includes/db.php');
include('../includes/log_module.php');

// 👉 Kinukuha ang registrar ID galing session kung naka-login
$registrar_id = $_SESSION['registrar_id'] ?? null;

// 👉 Kinukuha ang mga filter galing sa URL (GET method)
$filter_course = $_GET['course'] ?? '';
$filter_year = $_GET['year_level'] ?? '';
$filter_section = $_GET['section'] ?? '';

// 👉 Simula ng base query, laging totoo ang WHERE 1=1 para madali magdagdag ng AND
$query = "SELECT * FROM students WHERE 1=1";

// 👉 Prepare variables para sa binding
$params = [];
$types = '';

// 👉 Kung may course filter, idagdag sa query
if ($filter_course !== '') {
    $query .= " AND course = ?";
    $params[] = $filter_course;
    $types .= 's'; // string
}

// 👉 Kung may year level filter, idagdag sa query
if ($filter_year !== '') {
    $query .= " AND year_level = ?";
    $params[] = $filter_year;
    $types .= 's';
}

// 👉 Kung may section filter, idagdag sa query
if ($filter_section !== '') {
    $query .= " AND section = ?";
    $params[] = $filter_section;
    $types .= 's';
}

// 👉 I-sort ang results pataas by ID
$query .= " ORDER BY student_number ASC";

// 👉 I-prepare ang SQL statement
$stmt = $conn->prepare($query);

// 👉 Bind the parameters kung meron
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

// 👉 I-execute ang statement
$stmt->execute();

// 👉 Kunin ang result set
$result = $stmt->get_result();

// 👉 Kunin ang laman ng dropdowns (unique values)
$courses = $conn->query("SELECT DISTINCT course FROM students");
$years = $conn->query("SELECT DISTINCT year_level FROM students");
$sections = $conn->query("SELECT DISTINCT section FROM students");
?>

<!-- 👉 Page title -->
<h2>📋 Enrolled Students</h2>

<!-- 👉 Filter Form para i-filter ang listahan ng students -->
<form method="GET" action="registrar_dashboard.php" style="margin-bottom: 20px;">
    <!-- 👉 Hidden input para alam ng dashboard anong page iloload -->
    <input type="hidden" name="page" value="student">

    <!-- 👉 Course Filter Dropdown -->
    <label>Course:</label>
    <select name="course">
        <option value="">All</option>
        <?php while ($c = $courses->fetch_assoc()): ?>
            <option value="<?= $c['course'] ?>" <?= $filter_course === $c['course'] ? 'selected' : '' ?>>
                <?= $c['course'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- 👉 Year Level Filter Dropdown -->
    <label>Year Level:</label>
    <select name="year_level">
        <option value="">All</option>
        <?php while ($y = $years->fetch_assoc()): ?>
            <option value="<?= $y['year_level'] ?>" <?= $filter_year === $y['year_level'] ? 'selected' : '' ?>>
                <?= $y['year_level'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- 👉 Section Filter Dropdown -->
    <label>Section:</label>
    <select name="section">
        <option value="">All</option>
        <?php while ($s = $sections->fetch_assoc()): ?>
            <option value="<?= $s['section'] ?>" <?= $filter_section === $s['section'] ? 'selected' : '' ?>>
                <?= $s['section'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- 👉 Submit button para mag-apply ng filters -->
    <button type="submit">Filter</button>
</form>

<!-- 👉 Table ng list of enrolled students -->
<table class="clearance-table">
    <tr>
        <th>Student Number</th>
        <th>Full Name</th>
        <th>Course</th>
        <th>Year Level</th>
        <th>Section</th>
        <th>Term</th>
        <th>Action</th>
    </tr>

    <!-- 👉 Loop through each student record -->
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <!-- 👉 Display student details -->
            <td><?= $row['student_number'] ?></td>
            <td><?= $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'] ?></td>
            <td><?= $row['course'] ?></td>
            <td><?= $row['year_level'] ?></td>
            <td><?= $row['section'] ?></td>
            <td><?= $row['term'] ?></td>

            <!-- 👉 View grades button -->
            <td>
                <a href="view_grades.php?student_number=<?= $row['student_number'] ?>">View Grades</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- 👉 External CSS for layout -->
<link rel="stylesheet" href="code_layout.css">
