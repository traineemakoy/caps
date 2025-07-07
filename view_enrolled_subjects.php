<?php
include("../includes/db.php");


$student_number = $_SESSION['student_number'] ?? null;
if (!$student_number) die("Unauthorized access.");

// 🎯 Get all distinct year levels and terms for dropdown
$dropdown_years = $conn->query("SELECT DISTINCT year_level FROM offered_subjects ORDER BY year_level");
$dropdown_terms = $conn->query("SELECT DISTINCT term FROM offered_subjects ORDER BY term");

// 🎯 Get selected filters from GET
$selected_year = $_GET['year'] ?? '';
$selected_term = $_GET['term'] ?? '';

// 📌 Base SQL with filtering
$sql = "SELECT es.*, s.subject_code, s.subject_name, f.full_name, os.term, os.school_year 
        FROM enrolled_subjects es
        JOIN subjects s ON es.subject_id = s.subject_id
        JOIN offered_subjects os ON es.subject_id = os.subject_id
        JOIN faculty f ON os.faculty_number = f.faculty_number
        WHERE es.student_number = ?";
$params = [$student_number];
$types = "s";

if (!empty($selected_year)) {
    $sql .= " AND os.year_level = ?";
    $params[] = $selected_year;
    $types .= "s";
}
if (!empty($selected_term)) {
    $sql .= " AND os.term = ?";
    $params[] = $selected_term;
    $types .= "s";
}
$sql .= " ORDER BY os.school_year DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$subjects = $stmt->get_result();
?>

<h3>📂 My Enrolled Subjects</h3>

<form method="GET">
    <input type="hidden" name="page" value="view_enrolled_subjects">

    <label>Year Level:</label>
    <select name="year">
        <option value="">All</option>
        <?php while ($row = $dropdown_years->fetch_assoc()): ?>
            <option value="<?= $row['year_level'] ?>" <?= $selected_year == $row['year_level'] ? 'selected' : '' ?>>
                <?= $row['year_level'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Term:</label>
    <select name="term">
        <option value="">All</option>
        <?php while ($row = $dropdown_terms->fetch_assoc()): ?>
            <option value="<?= $row['term'] ?>" <?= $selected_term == $row['term'] ? 'selected' : '' ?>>
                <?= $row['term'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Filter</button>
</form>

<br>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
    <tr style="background: green; color: white;">
        <th>Subject Code</th>
        <th>Subject Name</th>
        <th>Professor</th>
        <th>Term</th>
        <th>School Year</th>
    </tr>
    <?php if ($subjects->num_rows > 0): ?>
        <?php while ($row = $subjects->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_code']) ?></td>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['term']) ?></td>
                <td><?= htmlspecialchars($row['school_year']) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5" style="text-align: center;">No enrolled subjects found.</td></tr>
    <?php endif; ?>
</table>

<link rel="stylesheet" href="../assets/css/dashboard.css">
