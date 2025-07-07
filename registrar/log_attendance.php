<?php
include('../includes/db.php');

// Get date filters
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Build base query
$sql = "
    SELECT al.*, f.full_name 
    FROM attendance_log al
    JOIN faculty f ON al.faculty_number = f.faculty_number
    WHERE 1
";

// If date range is provided
$params = [];
if ($from && $to) {
    $sql .= " AND al.date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}

$sql .= " ORDER BY al.time_in DESC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if ($from && $to) {
    $stmt->bind_param("ss", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- 🔍 Filter Form -->
<form method="GET">
    <input type="hidden" name="page" value="log_attendance">
    From: <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
    To: <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
    <button type="submit">Filter</button>
    <a href="?page=log_attendance"><button type="button">Reset</button></a>
</form>

<h2>Faculty Attendance Log</h2>
<table border="1" cellpadding="10">
    <tr style="background: #145A32; color: white;">
        <th>ID</th>
        <th>Faculty Number</th>
        <th>Full Name</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Customize Out</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['faculty_number'] ?></td>
        <td><?= $row['full_name'] ?></td>
        <td><?= $row['date'] ?></td>
        <td><?= $row['time_in'] ?? 'N/A' ?></td>
        <td><?= $row['time_out'] ?? 'N/A' ?></td>
        <td><?= $row['customize_out'] ?? 'N/A' ?></td>
    </tr>
    <?php endwhile; ?>
</table>
