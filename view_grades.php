<?php
include("../includes/db.php");

$student_number = $_SESSION['student_number'] ?? null;
if (!$student_number) die("Unauthorized access.");

// 📌 Get all records first
$query = $conn->prepare("SELECT 
    s.subject_code, 
    s.subject_name, 
    f.full_name AS professor,
    g.prelim_grade, 
    g.midterm_grade, 
    g.finals_grade, 
    g.grade AS official_grade, 
    g.remarks,
    COALESCE(c.status, 'Pending') AS clearance_status,
    os.term, 
    os.school_year,
    os.year_level
FROM enrolled_subjects es
JOIN subjects s ON es.subject_id = s.subject_id
JOIN offered_subjects os ON es.subject_id = os.subject_id
JOIN faculty f ON os.faculty_number = f.faculty_number
LEFT JOIN grades g ON es.student_number = g.student_number AND es.subject_id = g.subject_id
LEFT JOIN clearance c ON es.student_number = c.student_number AND es.subject_id = c.subject_id
WHERE es.student_number = ?");
$query->bind_param("s", $student_number);
$query->execute();
$result = $query->get_result();

// 📦 Group by: Year Level + Term
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $group_key = $row['year_level'] . ' - ' . $row['term'];
    $grouped[$group_key][] = $row;
}
?>

<h3>📊 Grades & Clearance</h3>

<?php foreach ($grouped as $group => $subjects): ?>
    <h4 style="color: green; margin-top: 20px;"><?= htmlspecialchars($group) ?></h4>
    <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
        <tr style="background: green; color: white;">
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Professor</th>
            <th>Prelim</th>
            <th>Midterm</th>
            <th>Finals</th>
            <th>Official Grade</th>
            <th>Remarks</th>
            <th>Clearance</th>
            <th>Term</th>
            <th>School Year</th>
        </tr>
        <?php foreach ($subjects as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject_code']) ?></td>
            <td><?= htmlspecialchars($row['subject_name']) ?></td>
            <td><?= htmlspecialchars($row['professor']) ?></td>
            <td><?= is_null($row['prelim_grade']) ? 'N/A' : $row['prelim_grade'] ?></td>
            <td><?= is_null($row['midterm_grade']) ? 'N/A' : $row['midterm_grade'] ?></td>
            <td><?= is_null($row['finals_grade']) ? 'N/A' : $row['finals_grade'] ?></td>
            <td><?= is_null($row['official_grade']) ? 'N/A' : $row['official_grade'] ?></td>
            <td><?= htmlspecialchars($row['remarks'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['clearance_status']) ?></td>
            <td><?= htmlspecialchars($row['term']) ?></td>
            <td><?= htmlspecialchars($row['school_year']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>

<link rel="stylesheet" href="../assets/css/dashboard.css">
