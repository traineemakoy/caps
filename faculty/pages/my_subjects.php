<?php
// Start ng session para ma-access ang session data (pang-login)

// Import ng DB connection
include("../includes/db.php");

// Kuhanin ang faculty number mula session
$faculty_number = $_SESSION['faculty_number'] ?? null;

// Kung walang nakalogin, ipakita unauthorized
if (!$faculty_number) {
    echo "<p>Unauthorized access.</p>";
    exit();
}

// Kuhanin ang mga selected values galing URL filters (GET)
$selected_sy = $_GET['school_year'] ?? '';      // Halimbawa: 2025–2026
$selected_course = $_GET['course'] ?? '';        // Halimbawa: BSIT
$selected_yl = $_GET['year_level'] ?? '';        // Halimbawa: 1st Year

// Kunin ang mga unique school years ng mga tinuturuan ni prof
$sy_options = $conn->query("SELECT DISTINCT school_year FROM offered_subjects WHERE faculty_number = '$faculty_number'");

// Kunin ang mga unique courses ng mga subjects ng prof
$course_options = $conn->query("SELECT DISTINCT course FROM offered_subjects WHERE faculty_number = '$faculty_number'");

// Kunin ang mga unique year levels ng subjects ng prof
$yl_options = $conn->query("SELECT DISTINCT year_level FROM offered_subjects WHERE faculty_number = '$faculty_number'");

// Gawa ng filter conditions para sa SQL query
$conditions = ["os.faculty_number = '$faculty_number'"]; // Default: own subjects lang

// Kapag may filter na napili, idagdag sa conditions
if (!empty($selected_sy)) $conditions[] = "os.school_year = '$selected_sy'";
if (!empty($selected_course)) $conditions[] = "os.course = '$selected_course'";
if (!empty($selected_yl)) $conditions[] = "os.year_level = '$selected_yl'";

// Pag-isa-isahin ang conditions para sa WHERE clause
$where = implode(" AND ", $conditions);

// Final SQL query: join offered_subjects + subjects table to get full info
$sql = "SELECT os.*, s.subject_code, s.subject_name
        FROM offered_subjects os
        JOIN subjects s ON os.subject_id = s.subject_id
        WHERE $where";

// Execute the query
$result = $conn->query($sql);
?>

<!-- Title ng section -->
<h2>📘 My Subjects</h2>

<!-- Filter Form -->
<form method="GET" action="">
    <!-- Hidden input para hindi mawala yung layout sa dashboard page -->
    <input type="hidden" name="page" value="my_subjects">

    <!-- Dropdown para sa School Year -->
    <label>School Year:</label>
    <select name="school_year" onchange="this.form.submit()">
        <option value="">All</option>
        <?php while ($sy = $sy_options->fetch_assoc()): ?>
            <option value="<?= $sy['school_year'] ?>" <?= ($selected_sy == $sy['school_year']) ? 'selected' : '' ?>>
                <?= $sy['school_year'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- Dropdown para sa Course -->
    <label>Course:</label>
    <select name="course" onchange="this.form.submit()">
        <option value="">All</option>
        <?php while ($c = $course_options->fetch_assoc()): ?>
            <option value="<?= $c['course'] ?>" <?= ($selected_course == $c['course']) ? 'selected' : '' ?>>
                <?= $c['course'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- Dropdown para sa Year Level -->
    <label>Year Level:</label>
    <select name="year_level" onchange="this.form.submit()">
        <option value="">All</option>
        <?php while ($yl = $yl_options->fetch_assoc()): ?>
            <option value="<?= $yl['year_level'] ?>" <?= ($selected_yl == $yl['year_level']) ? 'selected' : '' ?>>
                <?= $yl['year_level'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<!-- Ipakita ang table ng subjects kung may nakuha na rows -->
<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Term</th>
                <th>School Year</th>
            </tr>
        </thead>
        <tbody>
        <!-- Loop sa bawat subject record -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_code']) ?></td>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><?= htmlspecialchars($row['year_level']) ?></td>
                <td><?= htmlspecialchars($row['term']) ?></td>
                <td><?= htmlspecialchars($row['school_year']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <!-- Kapag walang result na nahanap -->
    <p>No subjects found.</p>
<?php endif; ?>



