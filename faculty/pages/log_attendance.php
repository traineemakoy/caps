<?php
include('../includes/db.php');
$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) exit("Unauthorized access");

// Default values
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$sql = "SELECT id, time_in, time_out, customize_out, date 
        FROM attendance_log 
        WHERE faculty_number = ?";
$params = [$faculty_number];

// Add date filter if both present
if ($from && $to) {
    $sql .= " AND date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}

$sql .= " ORDER BY time_in DESC";

$stmt = $conn->prepare($sql);
$types = str_repeat("s", count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- 🔍 Date Filter Form -->
<form method="GET">
    <input type="hidden" name="page" value="log_attendance">
    From: <input type="date" name="from" value="<?= $from ?>">
    To: <input type="date" name="to" value="<?= $to ?>">
    <button type="submit">Filter</button>
    <a href="?page=log_attendance"><button type="button">Reset</button></a>
</form>

<h2>My Attendance Logs</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Customize Out</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['date'] ?></td>
        <td><?= $row['time_in'] ?? 'N/A' ?></td>
        <td><?= $row['time_out'] ?? 'N/A' ?></td>
        <td><?= $row['customize_out'] ?? 'N/A' ?></td>
    </tr>
    <?php endwhile; ?>
</table>
